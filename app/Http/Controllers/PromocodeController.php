<?php

namespace App\Http\Controllers;

use App\Event;
use App\Promocode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromocodeController extends Controller
{
	/**
	 * Get all promocodes in DB
	 * 
	 * @param  Request $request
	 * @return Response (JSON)
	 */
	public function getPromocodes(Request $request)
	{
		$message = 'All promocodes';
		if($request->active == 1) {
			$promocodes = Promocode::whereIsActive(1)->get();
			$message = 'All Active promocodes';
		} else {
			$promocodes = Promocode::get();
		}

		$response = [
			'status' => 'success',
			'message' => $message,
			'data' => $promocodes
		];

		return response()->json($response, 200);
	}

	/**
	 * Validate the request and if all request params are valid
	 * generate a new promocode
	 *
	 * It is assumed that the promocode expires in 10 days but this can
	 * be accepted as a request param and the expires at can set accordingly.
	 * 
	 * @param  Request $request
	 * @return Response (JSON)
	 */
	public function generatePromocode(Request $request)
	{
		$validator = Validator::make($request->all(), Promocode::getValidationRules());

		if ($validator->fails()) {
			$errors = $validator->errors();
			$data['errors'] = $errors->all();
			$response = [
				'status' => 'error',
				'message' => 'Validation Errors',
				'data' => $data
			];
			return response()->json($response, 200);
		}

		$promocode = new Promocode;
		$promocode->code = $promocode->generateCode();
		$promocode->discount = $request->discount;
		$promocode->max_discount = $request->max_discount;
		$promocode->validity_radius = $request->validity_radius;
		$promocode->validity_radius_unit = $request->validity_radius_unit;
		$promocode->is_active = 1;
		$promocode->expires_at = date('Y-m-d', strtotime("+".$request->expires_in." days"));
		$promocode->save();

		$data['promocode'] = $promocode;

		$response = [
			'status' => 'success',
			'message' => $promocode->code." promocode generated",
			'data' => $data
		];
		return response()->json($response, 200);
	}

	/**
	 * Validate request params and check if promocode exists in DB.
	 * If promocode exists and is active check if origin OR destination are in range of 
	 * validity radius.
	 * 
	 * @param  Request $request
	 * @return Response (JSON)
	 */
	public function validatePromocode(Request $request)
	{
		$validator = Validator::make($request->all(), [
										'origin_lat' => 'required',
										'origin_lng' => 'required',
										'dest_lat' => 'required',
										'dest_lng' => 'required',
										'promocode' => 'required'
									]);

		if ($validator->fails()) {
			$errors = $validator->errors();
			$data['errors'] = $errors->all();
			$response = [
				'status' => 'error',
				'message' => 'Invalid request',
				'data' => $data
			];
			return response()->json($response, 200);
		}

		$valid_condition = [['is_active','=','1'],
							['expires_at','>',date('Y-m-d H:i:s')],
							['code','=',$request->promocode]]; 

		$promocode = Promocode::select('code','discount','max_discount','validity_radius','validity_radius_unit','expires_at')->where($valid_condition)->first();

		if(!empty($promocode)) {
			$valid_events = Event::select('event_code','longitude','latitude')->where([['is_active','=','1'],
									  ['start_date','<',date('Y-m-d H:i:s')],
									  ['end_date','>',date('Y-m-d H:i:s')]])->get();
			$min_distance = 0;
			$polyline = [];

			foreach ($valid_events as $key => $event) {
				//Check if validity radius unit is in 'miles'
				$miles = false;
				if($promocode->validity_radius_unit == 'miles')
					$miles = true;

				$origin_dist = $this->distance($event->latitude, $event->longitude, $request->origin_lat, $request->origin_lng, $miles);

				if($min_distance == 0) {
					$min_distance = $origin_dist;
					$polyline['event_lat'] = $event->latitude;
					$polyline['event_lng'] = $event->longitude;
					$polyline['user_lat'] = $request->origin_lat;
					$polyline['user_lng'] = $request->origin_lng;
				}

				if($origin_dist < $min_distance) {
					$min_distance = $origin_dist;
					$polyline['event_lat'] = $event->latitude;
					$polyline['event_lng'] = $event->longitude;
					$polyline['user_lat'] = $request->origin_lat;
					$polyline['user_lng'] = $request->origin_lng;
				}

				$dest_dist = $this->distance($event->latitude, $event->longitude, $request->dest_lat, $request->dest_lng, $miles);

				if($dest_dist < $min_distance) {
					$min_distance = $dest_dist;
					$polyline['event_lat'] = $event->latitude;
					$polyline['event_lng'] = $event->longitude;
					$polyline['user_lat'] = $request->dest_lat;
					$polyline['user_lng'] = $request->dest_lng;
				}
			}

			$data['polyline'] = $polyline;
			$data['distance'] = round($min_distance, 2)." ".$promocode->validity_radius_unit;

			if($min_distance <= $promocode->validity_radius) {
				$data['promocode'] = $promocode;
				$response = [
					'status' => 'success',
					'message' => 'Promocode valid',
					'data' => $data
				];
			} else {
				$response = [
					'status' => 'error',
					'message' => 'The promocode you entered is valid but the origin and destination is too far away from event location',
				];
			}
		} else {
			$response = [
				'status' => 'error',
				'message' => 'The promocode you entered is invalid OR expired',
			];
		}

		return response()->json($response, 200);
	}

	/**
	 * Calculate distance between two co-ordinates (latitude/longitude)
	 * 
	 * @param  integer $lat1
	 * @param  integer $lng1
	 * @param  integer $lat2
	 * @param  integer $lng2
	 * @param  boolean $miles
	 * @return float (distance in kms OR miles)
	 */
	private function distance($lat1 = 0, $lng1 = 0, $lat2 = 0, $lng2 = 0, $miles = true)
	{
		$pi80 = M_PI / 180;
		$lat1 *= $pi80;
		$lng1 *= $pi80;
		$lat2 *= $pi80;
		$lng2 *= $pi80;

		$r = 6372.797; // mean radius of Earth in km
		$dlat = $lat2 - $lat1;
		$dlng = $lng2 - $lng1;
		$a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
		$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
		$km = $r * $c;

		return ($miles ? ($km * 0.621371192) : $km);
	}
}
