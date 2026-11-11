<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubUnitType extends Model
{
    use HasFactory;
    protected $fillable = [
        'sub_unit_id',
        'type_name',
    ];

    // public function sub_unit()
    // {
    //     return $this->belongsTo(SubUnit::class);
    // }

    /**
     * The listed type a survey link's type stands for. Reports find forms by
     * the listed name only, but a link made before a type was renamed still
     * sends the old one ("CJB (PSTO-ZS)" for "CGB (PSTO-ZS)", "AAF" for
     * "AAF (PSTO-ZDS)"). An unlisted name is matched by its initials, or else
     * by its station when only one listed type has that station; a name that
     * matches nothing, or more than one, is kept as given.
     */
    public static function listedName($sub_unit_id, $name)
    {
        if (!$sub_unit_id || !$name) {
            return $name;
        }

        $names = self::where('sub_unit_id', $sub_unit_id)->pluck('type_name');
        if ($names->contains($name)) {
            return $name;
        }

        [$initials, $station] = self::nameParts($name);
        $byInitials = $names->filter(fn ($listed) => self::nameParts($listed)[0] === $initials);
        if ($byInitials->count() === 1) {
            return $byInitials->first();
        }
        $byStation = $station === '' ? collect() : $names->filter(fn ($listed) => self::nameParts($listed)[1] === $station);
        if ($byStation->count() === 1) {
            return $byStation->first();
        }

        return $name;
    }

    // "CGB (PSTO-ZS)" => ['CGB', 'PSTO-ZS'], "AAF" => ['AAF', '']
    private static function nameParts($name)
    {
        preg_match('/^\s*([^(]*?)\s*(?:\((.*?)\)?)?\s*$/', $name, $parts);

        return [strtoupper($parts[1] ?? ''), strtoupper(trim($parts[2] ?? ''))];
    }

}
