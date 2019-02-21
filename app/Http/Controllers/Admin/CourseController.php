<?php

namespace App\Http\Controllers\Admin;

use App\Components\Core\Utilities\Helpers;
use App\Components\Course\Models\Course;
use App\Components\Course\Repositories\CourseRepository;
use Illuminate\Http\Request;

class CourseController extends AdminController
{
    /**
     * @var CourseRepository
     */
    private $courseRepository;

    /**
     * CourseController constructor.
     * @param CourseRepository $courseRepository
     */
    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = $this->courseRepository->listCourses(request()->all());

        return $this->sendResponseOk($data,"list courses ok.");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate = validator($request->all(),[
            'name' => 'required',
            'email' => 'required|email|unique:courses,email',
            'password' => 'required|min:8',
            'permissions' => 'array',
            'groups' => 'array',
        ]);

        if($validate->fails()) return $this->sendResponseBadRequest($validate->errors()->first());

        /** @var Course $course */
        $course = $this->courseRepository->create($request->all());

        if(!$course) return $this->sendResponseBadRequest("Failed create.");

        // attach to group
        if($groups = $request->get('groups',[]))
        {
            foreach ($groups as $groupId => $shouldAttach)
            {
                if($shouldAttach) $course->groups()->attach($groupId);
            }
        }

        return $this->sendResponseCreated($course);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $course = $this->courseRepository->find($id,['groups']);

        if(!$course) return $this->sendResponseNotFound();

        return $this->sendResponseOk($course);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validate = validator($request->all(),[
            'name' => 'required',
            'email' => 'required|email',
            'permissions' => 'array',
            'groups' => 'array',
        ]);

        if($validate->fails()) return $this->sendResponseBadRequest($validate->errors()->first());

        $payload = $request->all();

        // if password field is present but has empty value or null value
        // we will remove it to avoid updating password with unexpected value
        if(!Helpers::hasValue($payload['password'])) unset($payload['password']);

        $updated = $this->courseRepository->update($id,$payload);

        if(!$updated) return $this->sendResponseBadRequest("Failed update");

        // re-sync groups

        /** @var Course $course */
        $course = $this->courseRepository->find($id);

        $groupIds = [];

        if($groups = $request->get('groups',[]))
        {
            foreach ($groups as $groupId => $shouldAttach)
            {
                if($shouldAttach) $groupIds[] = $groupId;
            }
        }

        $course->groups()->sync($groupIds);

        return $this->sendResponseUpdated();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // do not delete self
        if($id == auth()->course()->id) return $this->sendResponseForbidden();

        try {
            $this->courseRepository->delete($id);
        } catch (\Exception $e) {
            return $this->sendResponseBadRequest("Failed to delete");
        }

        return $this->sendResponseDeleted();
    }
}
