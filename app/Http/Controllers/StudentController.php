<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Major;
use Yajra\DataTables\Facades\DataTables;
use DateTime;

class StudentController extends Controller
{
    public function index(Request $req)
    {
        if($req->ajax()){
            $students = Student::select(['students.*', 'majors.name AS major_name'])->leftJoin('majors', 'majors.id', '=', 'students.major_id');
            return DataTables::of($students)
            ->addIndexColumn()
            ->addColumn('action', function ($data) {
                return '
                    <span onclick="show('. $data->id .')" class="edit-admin fas fa-eye text-info mr-1" style="font-size: 1.2rem; cursor: pointer" data-toggle="tooltip" title="Show"></span>
                    <span onclick="edit('. $data->id .')" class="edit-admin fas fa-pencil-alt text-warning mr-1" style="font-size: 1.2rem; cursor: pointer" data-toggle="tooltip" title="Edit"></span>
                    <span onclick="destroy('. $data->id .')" class="fas fa-trash-alt text-danger" style="font-size: 1.2rem; cursor: pointer" data-toggle="tooltip" title="Delete"></span>
                ';
            })
            ->addColumn('semester', function ($data) {
                if($data->batch_year){
                    $tahun = (new DateTime('today'))->diff((new DateTime($data->batch_year.'-07-01')))->y * 2;
                    $tahun += date('m') < 6 ? 2 : 1;
                    return $data->batch_year == date('Y') && $tahun == 2 ? 0 : $tahun;
                }
                return 0;
            })
            ->rawColumns(['action'])
            ->make(true)
            ;
        }

        $Majors = Major::all();
        return view('student.index', ['Majors' => $Majors]);
    }

    public function show($id)
    {
        $student = Student::with('major')->find($id);

        $tahun = 0;
        if($student->batch_year && $student->batch_year <= date('Y')){
            $tahun = (new DateTime('today'))->diff((new DateTime($student->batch_year.'-07-01')))->y * 2;
            $tahun += date('m') < 6 ? 2 : 1;
        }
        $student->semester = $student->batch_year == date('Y') && $tahun == 2 ? 0 : $tahun;

        return response()->json($student);
    }

    public function update($id, Request $req)
    {
        $student = Student::find($id);
        $student->name = $req->name;
        $student->major_id = $req->major_id;
        $student->class = $req->class;
        $student->batch_year = $req->batch_year;
        $student->save();
        return response()->json($student);
    }

    public function delete($id)
    {
        $student = Student::find($id);
        $student->delete();
        return response()->json($student);
    }

    public function store(Request $req)
    {
        $student = new Student;
        $student->name = $req->name;
        $student->major_id = $req->major_id;
        $student->class = $req->class;
        $student->batch_year = $req->batch_year;
        $student->save();
        return response()->json($student);
    }
}
