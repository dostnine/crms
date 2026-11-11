<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * The PSTO-ZS driver is CGB, not CJB. A survey link made when the type was still
 * seeded as "CJB (PSTO-ZS)" saved October 2026's forms under that name, and the
 * report for "CGB (PSTO-ZS)" left them out. Renames CJB to CGB in the Driving
 * type list (if it is still there) and on the forms; only the name changes, the
 * forms keep their dates. Safe to run more than once.
 *
 * Run it on its own (php artisan db:seed --class=DrivingTypeCgbSeeder --force);
 * it is not part of DatabaseSeeder.
 */
class DrivingTypeCgbSeeder extends Seeder
{
    const OLD_NAME = 'CJB (PSTO-ZS)';
    const NEW_NAME = 'CGB (PSTO-ZS)';

    public function run(): void
    {
        DB::transaction(function () {
            $renamed = 0;
            if (!DB::table('sub_unit_types')->where('type_name', self::NEW_NAME)->exists()) {
                $renamed = DB::table('sub_unit_types')->where('type_name', self::OLD_NAME)->update(['type_name' => self::NEW_NAME]);
            }

            $subUnitIds = DB::table('sub_unit_types')->where('type_name', self::NEW_NAME)->pluck('sub_unit_id');
            $forms = DB::table('c_s_f_forms')
                ->whereIn('sub_unit_id', $subUnitIds)
                ->where('sub_unit_type', self::OLD_NAME)
                ->update(['sub_unit_type' => self::NEW_NAME]);

            $this->command?->info("Driving types renamed: {$renamed}. Forms moved from " . self::OLD_NAME . ' to ' . self::NEW_NAME . ": {$forms}.");
        });
    }
}
