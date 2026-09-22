<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string|null $user_name
 * @property string $action
 * @property string|null $description
 * @property string|null $ip_address
 * @property string $created_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUserName($value)
 */
	class ActivityLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $name_ms
 * @property string $slug
 * @property string|null $icon
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereNameMs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSlug($value)
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $farm_name
 * @property string $kedah_district
 * @property string|null $farm_location_details
 * @property string|null $farming_certification
 * @property string|null $cert_number
 * @property int|null $experience_years
 * @property numeric|null $rating
 * @property string|null $famox_tier
 * @property string|null $avatar
 * @property string|null $quote
 * @property int|null $is_approved
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Farmer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Farmer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Farmer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Farmer whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Farmer whereCertNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Farmer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Farmer whereExperienceYears($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Farmer whereFamoxTier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Farmer whereFarmLocationDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Farmer whereFarmName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Farmer whereFarmingCertification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Farmer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Farmer whereIsApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Farmer whereKedahDistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Farmer whereQuote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Farmer whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Farmer whereUserId($value)
 */
	class Farmer extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string $order_number
 * @property string $buyer_name
 * @property string|null $buyer_role
 * @property string|null $phone
 * @property numeric $total_amount
 * @property string|null $status
 * @property string|null $payment_method
 * @property string $shipping_address
 * @property string|null $notes
 * @property string|null $batch_code
 * @property string|null $driver
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBatchCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBuyerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBuyerRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUserId($value)
 */
	class Order extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $order_id
 * @property string $product_name
 * @property numeric $quantity
 * @property string|null $unit
 * @property numeric $price
 * @property numeric $subtotal
 * @property string $created_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereUnit($value)
 */
	class OrderItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $crop_name
 * @property numeric $direct_price
 * @property numeric $middleman_price
 * @property string $farmer_gain
 * @property string $trend
 * @property string $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceIndex newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceIndex newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceIndex query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceIndex whereCropName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceIndex whereDirectPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceIndex whereFarmerGain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceIndex whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceIndex whereMiddlemanPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceIndex whereTrend($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceIndex whereUpdatedAt($value)
 */
	class PriceIndex extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $batch_id
 * @property int $farmer_id
 * @property int $category_id
 * @property string $name
 * @property string $name_ms
 * @property string $slug
 * @property string|null $description
 * @property string|null $description_ms
 * @property string $harvest_date
 * @property string|null $expiry_date
 * @property numeric $quantity_available
 * @property numeric $minimum_order
 * @property string $unit
 * @property numeric $price_per_unit
 * @property numeric $middleman_price
 * @property string|null $storage_temp
 * @property string|null $farm_location
 * @property string|null $grade
 * @property string|null $pesticide_status
 * @property string|null $image_path
 * @property int|null $is_spotlight
 * @property string|null $spotlight_headline
 * @property string|null $spotlight_subtitle
 * @property string|null $tag
 * @property string|null $approval_status
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\Farmer|null $farmer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereApprovalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescriptionMs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereFarmLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereFarmerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereHarvestDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsSpotlight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMiddlemanPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMinimumOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereNameMs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePesticideStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePricePerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereQuantityAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSpotlightHeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSpotlightSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStorageTemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUnit($value)
 */
	class Product extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $role_id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $avatar
 * @property int|null $is_active
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRoleId($value)
 */
	class User extends \Eloquent {}
}

