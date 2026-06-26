<?php

namespace App\Http\Controllers;

use App\Models\ExamData;
use App\Http\Requests\StoreExamDataRequest;
use App\Http\Requests\UpdateExamDataRequest;
use App\Mail\StudentScolarshipExamMail;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ExamDataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExamDataRequest $request, Student $student)
    {
        $data = $request->validated();
        $data['student_id'] = $student->id;

        DB::beginTransaction();
        try {

            ExamData::create($data);
            DB::commit();

            $this->sendEmail($student);
            return back()->with('success', 'Data ujian berhasil dikirim ke email mahasiswa');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan data ujian' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ExamData $examData)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExamData $examData)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExamDataRequest $request, ExamData $examData)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExamData $examData)
    {
        //
    }

    public function sendEmail(Student $student)
    {
        $student->load('user', 'examData');
        // Memformat tanggal menjadi 31 Desember 2024
        $formattedDate = Carbon::parse($student->examData->date)->translatedFormat('d F Y');

        // Tambahkan tanggal yang sudah diformat ke data yang akan dikirim ke email
        $student->examData->date = $formattedDate;

        try {
            Mail::to($student->user->email)->send(new StudentScolarshipExamMail($student));
            session()->flash('success', 'Email berhasil dikirim');
        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());
        }
    }
}
