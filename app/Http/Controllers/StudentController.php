<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Student;
use App\Role;
use App\University;
use App\Stackholder;
use App\Verification;
use App\Http\Controllers\Auth\RegisterController;

class StudentController extends Controller
{
    //

    public function showStudentAddForm(){
        return view('user_dashboard.manage_add_student');
    }

    public function searchStudentView() {
        return view('search_student');
    }

    public function searchStudent(Request $request) {
        $student_info = Student::where('registration_no', $request->registration_no)->first();
        if($student_info == null) {
            flash('Registration number does not exist');
            return redirect()->route('stakeholder.search');
        }
        $user_info = User::where('id', $student_info->user_id)->first();
        if($user_info == null) {
            flash('User does not exist');
            return redirect()->route('stakeholder.search');
        }

        if ($user_info->email != $request->email) {
            flash('Email does not match with the Registration Number');
            return redirect()->route('stakeholder.search');
        }
        if ($student_info->date_of_birth != $request->date_of_birth) {
            flash('Date of Birth does not match with the registration number');
            return redirect()->route('stakeholder.search');
        }
        if ($student_info->university_id != $request->university_id) {
            flash('University name does not match with the registration number');
            return redirect()->route('stakeholder.search');
        }
        return redirect()->route('stakeholder.payment_request', ['registration_no' => $student_info->registration_no]);
    }

    public function paymentRequestView(Request $request, $registration_no) {
        $student_info = Student::where('registration_no', $registration_no)->first();
        $user_info = User::where('id', $student_info->user_id)->first();
        $university_info = University::where('id', $student_info->university_id)->first();
        return view('payment_request', [
            'student' => $student_info,
            'user' => $user_info,
            'university' => $university_info]);
    }

    public function storePaymentRequest(Request $request, $registration_no) {
        $this->validate($request, [
            'name' => 'required|string|max:30',
            'institute' => 'required|string|max:50',
            'designation' => 'required|string|max:20',
            'email' => 'required|email|max:30',
            'country' => 'required|string|max:30'
        ]);

        $student = Student::where('registration_no', $registration_no)->first();

        if($student == null) {
            flash('User not found');
            return redirect()->route('stakeholder.search');
        }

        $stakeholder = new Stackholder;
        $stakeholder->name = $request->name;
        $stakeholder->institute = $request->institute;
        $stakeholder->email = $request->email;
        $stakeholder->designation = $request->designation;
        $stakeholder->country = $request->country;

        $stakeholder->save();

        $verification = new Verification;
        $verification->student_id = $student->id;
        $verification->stackholder_id = $stakeholder->id;
        $verification->verification_status = "Requested";
        $verification->save();

        flash('Successfully requested!')->success();

        return redirect()->route('stakeholder.search');

    }

    public function storeStudent(Request $request){

        $this->validate($request, [
            'first_name' => 'required|string|max:20',
            'last_name' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:users',
            'mobile_no' => 'required|string|max:11',
            'university_id' => 'required|integer',
            'department_id' => 'required|integer',
            'date_of_birth' => 'required|date'
        ]);

        $user = new User;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->mobile_no = $request->mobile_no;
        $user->role_id = 2;
        $user->is_activated = false; 

        $user->save();

        $student = new Student;
        $student->user_id = $user->id;
        $student->university_id = $request->university_id;
        $student->department_id = $request->department_id;
        $student->registration_no = $request->registration_no;
        $student->session = $request->session_no;
        $student->date_of_birth = $request->date_of_birth;

        $student->save();

        $registerController = new RegisterController();
        $registerController->sendActivationCode($user);

        flash('Student successfully added!')->success();

        return redirect()->route('student.add');

    }
}
