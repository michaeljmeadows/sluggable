<?php

namespace michaeljmeadows\Traits;

use ErrorException;
use Illuminate\Database\Eloquent\Model;

trait Sluggable
{
    public static function bootCreatedBy()
    {
        static::creating(function (Model $model) {
            $model->setSlug();
        });

        static::updating(function (Model $model) {
            $model->setSlug();
        });
    }

    public function getInvalidSlugs(): array
    {
        return $this->invalidSlugs ?? [
            'create',
        ];
    }

    public function getSlugField(): string
    {
        return $this->slugField ?? 'slug';
    }

    public function getSlugFields(): array
    {
        if (! $this->slugFields ?? false) {
            throw new ErrorException('Sluggable model has no slug fields. Check $slugFields is set in '.static::class);
        }

        if (in_array($this->getSlugField(), $this->slugFields)) {
            throw new ErrorException('Sluggable model cannot generate slug from its own slug field.');
        }

        return $this->slugFields;
    }

    public function setSlug(): void
    {
        if ($this->{$this->getSlugField()} != null && $this->isClean($this->getSlugFields())) {
            return;
        }

        foreach ($this->getSlugFields() as $attribute) {
            $string = ($string ?? false)
                ? $string->append(' ')
                    ->append($this->$attribute)
                : str($this->$attribute);
        }
        $slug = $string->slug();

        $existingSlugs = static::withTrashed()
            ->where('id', '!=', $this->id)
            ->select($this->getSlugField())
            ->pluck($this->getSlugField());

        $appendNumber = 1;
        $originalSlug = $slug;
        do {
            $allChecked = true;
            if (in_array($slug, $this->getInvalidSlugs())) {
                $allChecked = false;
            } else {
                foreach ($existingSlugs as $existingSlug) {
                    if ($slug == $existingSlug) {
                        $allChecked = false;
                        break;
                    }
                }
            }
            if (! $allChecked) {
                $slug = $originalSlug->append('-')
                    ->append($appendNumber);
                $appendNumber++;
            }
        } while (! $allChecked);

        $this->slug = $slug->toString();
    }
}
