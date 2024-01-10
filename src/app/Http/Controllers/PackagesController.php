<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Storage;
use App\DataTransferObjects\PackageDto;
use OpenAI\Responses\Threads\Runs\ThreadRunResponse;
use OpenAI\Responses\Threads\ThreadResponse;

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
        
        $dedicatedId    = $request->input('dedicated_id');
        $newPackageData = $request->input('package');

        if (empty($dedicatedId)) {
            return response()->json([
                'error' => 'You tried saving an package without `dedicated_id`.'
            ]);
        }

        if (empty($newPackageData)) {
            return response()->json([
                'error' => 'You tried saving an empty `package`.'
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
        $thread = OpenAI::threads()->createAndRun([
            'assistant_id' => 'asst_Kx44RNw6tcMPGrFRO3ad4FNU',
            'thread' => [
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $newPackageData,
                    ],
                ],
            ],
        ]);

        $result = $this->loadAnswer($thread);

        return $result;
    }

    private function loadAnswer(ThreadRunResponse $threadRun)
    {
        while(in_array($threadRun->status, ['queued', 'in_progress'])) {
            $threadRun = OpenAI::threads()->runs()->retrieve(
                threadId: $threadRun->threadId,
                runId: $threadRun->id,
            );
        }

        if ($threadRun->status !== 'completed') {
            return 'Request failed, please try again';
        }

        $messageList = OpenAI::threads()->messages()->list(
            threadId: $threadRun->threadId,
        );

        return $messageList->data[0]->content[0]->text->value;
    }

    // private function sanitize(string $str) {

    //     $str = str_replace('```', '', $str);
    //     $str = str_replace('\n', '', $str);
    //     $str = str_replace('\\', '', $str);

    //     return $str;
    // }
}