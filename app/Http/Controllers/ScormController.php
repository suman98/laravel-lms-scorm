<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use  Peopleaps\Scorm\Manager\ScormManager;
use Peopleaps\Scorm\Model\ScormModel;

class ScormController extends Controller
{
    /** @var ScormManager $scormManager */
    private $scormManager;
    /**
     * ScormController constructor.
     * @param ScormManager $scormManager
     */
    public function __construct(ScormManager $scormManager)
    {
        $this->scormManager = $scormManager;
    }

    public function show($id)
    {
        $item = ScormModel::with('scos')->find($id);
       return $item;
        // response helper function from base controller reponse json.
        return $this->respond($item);
    }

    public function store(Request $request)
    {

        $scorm = $this->scormManager->uploadScormArchive($request->file('file'));
        dd($scorm);
        dd('done');
        // try {
        //     $scorm = $this->scormManager->uploadScormArchive($request->file('file'));

        //     // handle scorm runtime error msg
        // } catch (InvalidScormArchiveException | StorageNotFoundException $ex) {
        //     return $this->respondCouldNotCreateResource(trans('scorm.' .  $ex->getMessage()));
        // }

        // // response helper function from base controller reponse json.
        // return $this->respond(ScormModel::with('scos')->whereUuid($scorm['uuid'])->first());
    }

    public function saveProgress(Request $request)
    {
        // TODO save user progress...
    }
}
