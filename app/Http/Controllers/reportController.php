<?php
namespace App\Http\Controllers;

use App\Exports\ReportAllExport;
use App\Exports\ReportEmployeeInAdminExport;
use App\Exports\ReportExport;

use App\Models\employeeProject;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class reportController extends Controller
{
    protected $user;
    protected $employeeProject;
    public function __construct()
    {
        $this->user = new User();
        $this->employeeProject = new employeeProject();
    }
    public function index()
    {
        return view('report.index');
    }
    /**
     * Summary of export one
     * @return void
     * @param $data =year and month
     */
    public function export($data)
    {
        $arrYearAndMonth = explode("-", $data); //Gets year and month
        $name = Auth::user()->name;
        $slug = Str::slug($name, '-');
        $dt = Carbon::parse($data);
        return Excel::download(
            new ReportExport(
                $arrYearAndMonth[0],
                $arrYearAndMonth[1],
                    $dt->daysInMonth
            ),
            "$slug's-timekeeping-$data.xlsx"
        );
    }
    /**
     * Summary of exportTableDetal while you are admin
     * @return void
     */
    public function exportTableDetal($data)
    {
        $arrYearAndMonth = explode("-", $data); //Gets year and month
        $dt = Carbon::parse($data);
        $name = $this->user->where('id', $arrYearAndMonth[2])->first()->name;
        $slug = Str::slug($name, '-');
        return Excel::download(
            new ReportEmployeeInAdminExport(
                $arrYearAndMonth[0],
                $arrYearAndMonth[1], $arrYearAndMonth[2], $dt->daysInMonth
            ),
            "$slug's-timekeeping-$arrYearAndMonth[0]-$arrYearAndMonth[1].xlsx"
        );
    }
    /**
     * Summary of exportAll
     * @return void
     */
    public function exportAll()
    {
        return Excel::download(new ReportAllExport, 'exportal.xlsx');
    }
    /**
     * Summary of exportPDF
     * @return void
     */
    public function exportPDF($data)
    {
        $arrYearAndMonth = explode("-", $data); //Gets year and month
        $dt = Carbon::parse($data);
        $name = $this->user->where('id', $arrYearAndMonth[2])->first()->name;
        $list = $this->employeeProject
            ->join('projects', 'projects.id', 'employee_project.project_id')
            ->where(['employee_project.user_id' => $arrYearAndMonth[2]])
            ->whereMonth('employee_project.day_work', $arrYearAndMonth[1])
            ->whereYear('employee_project.day_work', $arrYearAndMonth[0])
            ->select('projects.name', 'working_hours', 'day_work')
            ->get();
        $dataView = [
            'list' => $list,
            'year' => $arrYearAndMonth[0],
            'month' => $arrYearAndMonth[1],
            'name' => $name,
            'daysInMonth' => $dt->daysInMonth
        ];
        $slug = Str::slug($name, '-');

        $pdf = PDF::loadView('index', $dataView);

        return $pdf->download("$slug's-timekeeping-$arrYearAndMonth[0]-$arrYearAndMonth[1].pdf");
    }
}
