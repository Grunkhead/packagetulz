<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Storage;
use App\DataTransferObjects\PackageDto;

class PackagesController {

    /**
     * Returns the list of packages as json.
     *
     * @return void
     */
    public function index()
    {
        $contents = Storage::get('packages.json');

        return response(content: $contents, status: 200, headers: [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Adds a package to the json file.
     *
     * @return void
     */
    public function add(Request $request) 
    {
        
        $newPackageData = $request->input('package');

        if (empty($newPackageData)) {
            return response()->json([
                'error' => 'You tried saving an empty package.'
            ]);
        }

        $packages = json_decode(
            Storage::get('packages.json')
        );

        try {
            $result = $this->parsePackageStringToJson($newPackageData);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }

        return response()->json($result); // debug

        $packageListUpdated = Storage::put('packages.json', json_encode($packages));

        if (!$packageListUpdated) {
            return response()->json([
                'error' => 'Failed saving the new package.'
            ]);
        }

        return response()->json($result);
    }

    /**
     * Removes a package from the json file.
     *
     * @return void
     */
    public function remove() 
    {

    }

    private function parsePackageStringToJson(string $newPackageData)
    {
        $result = OpenAI::completions()->create([
            'model' => 'gpt-3.5-turbo',
            'prompt' => sprintf(<<<EOD
            Format the data in to this structure.

            Structure:
            %s

            Data to be formatted:
            %s
            EOD, $this->getPackageStruct(), $newPackageData),
        ]);

        return $result;
    }

    private function getPackageStruct()
    {
        return <<<EOD
        Specifications -> Name, CPU, Memory, Storage, Traffic, OS, Uplink, Guaranteed Speed, DDoS Shield, Remote Management, Support
        Payment -> Payment Period, Payment Status
        Other Notes -> ...
        EOD;
    }
}