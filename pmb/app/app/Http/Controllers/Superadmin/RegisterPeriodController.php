<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\RegisterPeriod;
use App\Http\Requests\StoreRegisterPeriodRequest;
use App\Http\Requests\UpdateRegisterPeriodRequest;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class RegisterPeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $registerPeriods = RegisterPeriod::all();
        return view('pages.superadmin.register-period.index', compact('registerPeriods'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRegisterPeriodRequest $request)
    {
        $request->validated();

        try {
            RegisterPeriod::create($request->all());
            return redirect()->route('superadmin.register-periode.index')->with('success', 'Register period created successfully');
        } catch (\Exception $e) {
            return redirect()->route('superadmin.register-periode.index')->with('error', 'Something went wrong');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRegisterPeriodRequest $request, $id)
    {
        $request->validated();
        DB::beginTransaction();
        try {
            $registerPeriod = RegisterPeriod::find($id);
            $registerPeriod->update($request->all());
            
            DB::commit();
            return redirect()->route('superadmin.register-periode.index')->with('success', 'Register period updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('superadmin.register-periode.index')->with('error', 'Something went wrong');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $registerPeriod = RegisterPeriod::find($id);
            $registerPeriod->delete();
            DB::commit();
            return redirect()->route('superadmin.register-periode.index')->with('success', 'Register period deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('superadmin.register-periode.index')->with('error', 'Something went wrong');
        }
    }

    /**
     * Assign register period to all student
     */
    public function assignRegisterPeriodAllStudent()
    {
        DB::beginTransaction();
        try {
            $registerPeriod = RegisterPeriod::where('is_active', 1)->first();
            $students = Student::all();
            foreach ($students as $student) {
                $student->register_period_id = $registerPeriod->id;
                $student->save();
            }
            DB::commit();
            return redirect()->route('superadmin.register-periode.index')->with('success', 'Register period assigned to all student successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('superadmin.register-periode.index')->with('error', 'Something went wrong');
        }
    }
}
