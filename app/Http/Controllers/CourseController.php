<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Course;

class CourseController extends Controller
{
    
    public function manageCourses(){
        return view('user_dashboard.manage_courses');
    }

    public function showCourseAddForm(){
        return view('course.create');
    }


    public function storeCourse(Request $request){

        $this->validate($request, [
            'department_id' => 'required|integer',
            'semester_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20',
            'credit' => 'required|integer'
        ]);

        $course = new Course;
        $course->department_id = $request->department_id;
        $course->semester_no = $request->semester_no;
        $course->name = $request->name;
        $course->code = $request->code;
        $course->credit = $request->credit;

        $course->save();

        flash('Course successfully added!')->success();

        return redirect()->route('course.create');

    }
}
