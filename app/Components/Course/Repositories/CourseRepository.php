<?php
/**
 * Created by PhpStorm.
 * Course: darryl
 * Date: 10/6/2017
 * Time: 6:37 AM
 */

namespace App\Components\Course\Repositories;


use App\Components\Core\BaseRepository;
use App\Components\Course\Models\Course;
use App\Components\Core\Utilities\Helpers;

class CourseRepository extends BaseRepository
{
    public function __construct(Course $model)
    {
        parent::__construct($model);
    }

    /**
     * list all courses
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model[]|mixed[]
     */
    public function listCourses($params)
    {
        return $this->get($params,['groups'],function($q) use ($params)
        {
            $q->ofGroups(Helpers::commasToArray($params['group_id'] ?? ''));
            $q->ofName($params['name'] ?? '');
            $q->ofEmail($params['email'] ?? '');

            return $q;
        });
    }

    /**
     * delete a course by id
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function delete(int $id)
    {
        $ids = explode(',',$id);

        foreach ($ids as $id)
        {
            /** @var Course $Course */
            $Course = $this->model->find($id);

            if(!$Course)
            {
                return false;
            };

            $Course->groups()->detach();
            $Course->delete();
        }

        return true;
    }
}
