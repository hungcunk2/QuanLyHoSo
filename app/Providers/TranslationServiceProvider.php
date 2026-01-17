<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            // Check if database connection is available
            if (!\Illuminate\Support\Facades\DB::connection()->getPdo()) {
                return;
            }
            
            Cache::rememberForever('translations', function () {
                $translations = collect();
                $language_option = ["ar", "nl", "en", "fr", "de", "hi", "it", "vn"];

                try {
                    if (Schema::hasTable('settings')) {
                        if (\Session::get('setup_data') == '') {
                            // $setup_data = sitesetupSession('get');
                            // if ($setup_data) {
                            //     $language_option = $setup_data->language_option;
                            // }
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore if table doesn't exist
                }
                
                foreach ($language_option as $locale) {
                    $translations[$locale] = [
                        'php' => $this->phpTranslations($locale),
                        'json' => $this->jsonTranslations($locale),
                    ];
                }

                return $translations;
            });
        } catch (\Exception $e) {
            // Ignore errors if database is not configured or tables don't exist yet
        }
    }

    private function phpTranslations($locale)
    {
        $path = resource_path("lang/$locale");

        if (!File::exists($path)) {
            return [];
        }

        return collect(File::allFiles($path))->flatMap(function ($file) use ($locale) {
            $key = ($translation = $file->getBasename('.php'));

            return [$key => trans($translation, [], $locale)];
        });
    }

    private function jsonTranslations($locale)
    {
        $path = resource_path("lang/$locale.json");

        if (is_string($path) && is_readable($path)) {
            return json_decode(file_get_contents($path), true);
        }

        return [];
    }
}
