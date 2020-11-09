# Promocode Case Study
A company wants to give out promo codes worth **x** amount during events so people can get free rides to and from the event. The flaw with that is people can use the promo codes without going to the event.
The Promocode API can be used to

1. Generate new promocodes
2. Get generated promocodes
3. Validate promocodes

Promocodes is only valid when user’s pickup or destination is within **x** radius of the event venue. The promocode radius and expiry date is configurable. 

This API is built with Laravel 7.x

### Prerequisites

1. ```Composer```
2. ```PHP >= 7.2.5```
3. ```MySQL```

### Quick start

1. Clone the repository with `git clone https://github.com/mangya/promocodes.git <your_project_folder_name>`
2. Change directory to your project folder `cd <your_project_folder_name>`
3. Install the dependencies with `composer install`
4. Create database in MySQL.
5. Update the your database name and credentials in the `.env` file.
6. Create database tables and sample data with `php artisan migrate:refresh --seed`
7. Run the application with `php artisan server` (MySQL service should be up and running).
8. Access `http://localhost:8000` and you're ready to go!

## Packages used
* [Sanctum](https://laravel.com/docs/7.x/sanctum) — Laravel Sanctum provides a  authentication system for token based APIs.

## API Endpoints

All API requests require the use of a bearer roken in the Authorization header.

#### Get access token
```http
POST /api/get-access-token
```
| Parameter | Type | Description |
| :--- | :--- | :--- |
| `email` | `string` | **Required**. Your Email |
| `password` | `string` | **Required**. Your Password |
| `device_name` | `string` | **Required**. Your Device Name (Any String) |

#### Generate promocodes
```http
POST /api/promocodes/generate
```
| Parameter | Type | Description |
| :--- | :--- | :--- |
| `discount` | `integer` | **Required**. Discount in % |
| `max_discount` | `integer` | **Required**. Maximum discount limit |
| `validity_radius` | `integer` | **Required**. Promocode valid in radius |
| `validity_radius_unit` | `string` | **Required**. "kms" OR "miles" |
| `expires_in` | `integer` | **Required**. Expires in days |

#### Validate promocode
```http
POST /api/promocodes/validate
```
| Parameter | Type | Description |
| :--- | :--- | :--- |
| `origin_lat` | `integer` | **Required**. Origin Latitude |
| `origin_lng` | `integer` | **Required**. Origin Longitude |
| `dest_lat` | `integer` | **Required**. Destination Latitude |
| `dest_lng` | `string` | **Required**. Destination longitude |
| `promocode` | `integer` | **Required**. Promocode to be applied |

#### Get all OR Active promocodes
```http
POST /api/promocodes/get
```
| Parameter | Type | Description |
| :--- | :--- | :--- |
| `active` | `integer` | *(Optional)*. 1 to retrieve active promocodes |

## Responses
All enpoints returns a JSON response in the following format:

```javascript
{
  "message" : string,
  "status" : string,
  "data" : array
}
```

The `message` attribute contains a message commonly used to indicate descrption of errors

The `status` attribute describes if the transaction was successful or not. Values will be 'success' OR 'error'

The `data` attribute contains any other metadata associated with the response.

Thank you 😊