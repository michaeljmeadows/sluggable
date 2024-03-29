# michaeljmeadows/sluggable

A simple trait to generate model slugs.

## Installation 

You can install the package via composer:

```
composer require michaeljmeadows/sluggable
```

## Usage

Add a slug field to the migration for the model for which you'd like to generate slugs:

```php
$table->string('slug')->unique();
```

Once the migration has been updated, you can simply include the trait in your model's definition and define which other fields should be used with a protected array `$slugFields`:

```php
<?php

namespace App\Models;

use michaeljmeadows\Traits\Sluggable;
use Illuminate\Database\Eloquent\Model;

class NewModel extends Model
{
    use Sluggable;

    protected array $slugFields = [
        'name',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }	
```

We recommend not clearing the slug when soft-deleting a model.

#### Duplicate Slugs
It is possible that a generated slug has already been used in your model table, in which case Sluggable will append a number of increasing value until a unique slug has been found.

e.g. If `my-slug` is generated but already in the database for a different model, `my-slug-1` will be tried. If that is also taken, `my-slug-2` is tried, etc.

We recommend that you pick fields that are less likely to result in conflicts.

#### Soft-Deleting
If you're applying Sluggable to a model that can be soft-deleted, we recommend that slugs not be altered when deleted. Sluggable will handle potential conflicts with those models too.

### Customising Behaviour

#### Slug Field
By default, Sluggable expects the slug field to be named `slug`, but this can be customised by adding a protected string attribute `$slugField`:

```php
<?php

namespace App\Models;

use michaeljmeadows\Traits\Sluggable;
use Illuminate\Database\Eloquent\Model;

class NewModel extends Model
{
    use Sluggable;
	
    protected string $slugField = 'url_identifier'; // Instead of 'slug'.
```

#### Multiple Slug Fields
You can use as many fields in the `$slugFields` array as you'd like. These are included in the slug in the order in which they appear in the array:

```php
<?php

namespace App\Models;

use michaeljmeadows\Traits\Sluggable;
use Illuminate\Database\Eloquent\Model;

class NewModel extends Model
{
    use Sluggable;

    protected array $slugFields = [
        'given_name',
        'family_name',
    ];
```

#### Invalid Slugs 
By default, the only invalid slug in addition to those already in the database table is `create`. This is to prevent conflicts with resource controllers. If you'd like to customise the list of invalid slugs, you can add a protected array attribute `$invalidSlugs` to your model:

```php
<?php

namespace App\Models;

use michaeljmeadows\Traits\Sluggable;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use Sluggable;
	
    protected array $invalidSlugs = [
        'admin',  
        'create',  
    ];
```

We recommend that you always include `create` as an invalid slug for any model that uses a resource controller.