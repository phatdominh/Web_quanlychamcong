<?php
namespace App\Http\Controllers\tablet;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Constraint\Count;
class checkoutController extends Controller
{
  /**
   * Functions View Login=>index
   * Functions Submit checkout and logout=>checkout
   * Functions listProject
   */
  protected $project;
  public function __construct()
  {
    $this->project = new Project();
  }
  public function index()
  {
    try {
      $users = User::where('tablet_users.id', Auth::guard("tablet_users")->id())
        ->join("tablet_users", "tablet_users.user_id", "users.id")->with('Roles')->first();
      if ($users->roles[0]->pivot->role_id == '1' or $users->roles[0]->pivot->role_id == '2') {
        $project = $this->project::where('status', '0')->get();
        $user = User::where("tablet_users.id", Auth::guard('tablet_users')->id())
          ->join("tablet_users", "tablet_users.user_id", "users.id")
          ->select("tablet_users.email as emp_code", "users.name as fullname", "users.id as id")->first();
        $data = [
          'user' => $user,
          'project' => $project,
        ];
        return view("tablet.checkout", $data);
      }
      Auth::guard('tablet_users')->logout();
      session()->flash('error', 'Nhập không đúng vui lòng thử lại!');
      return redirect()->route('tabletLoginGet');
    } catch (\Throwable $th) {
      abort(500);
    }
  }
  public function checkout(Request $request)
  {
    try {
      $data = [
        'day_work' => $request->dayWork,
        'hours' => $request->hours,
        'id' => $request->id,
        'projects' => $request->projects,
      ];
      $arrYearAndMonth = explode('/', $data['day_work']);
      $month = $arrYearAndMonth;
      $getProject = array_filter($data['projects']); //Choose is not null
      $getHours = array_filter($data['hours']); //Choose is not null
      $checkHourMax24 = array_sum($getHours); // Sum hours
      $getProjectUnique = array_unique($data['projects']); //Check Unique
      if ($getProject == [] && $getHours == []) {
        $request->session()->flash('message', 'Không được để trống!');
        return back();
      } //Check Null
      if ($getProject == []) {
        $request->session()->flash('message', 'Dự án không được để trống!');
        return back();
      }
      if ($getHours == []) {
        $request->session()->flash('message', 'Số giờ không được để trống!');
        return back();
      }
      if ($checkHourMax24 > 24) {
        $request->session()->flash('message-hours', 'Tổng số giờ đã vượt quá 24h!');
        return back();
      } //Check Max 24 hours
      /************************* */
      if ($getProject == $getProjectUnique && $getHours == $data['hours']) {
        for ($i = 0; $i < count($data['projects']); $i++) {
          if ($data['hours'][$i] < 0) {
            $request->session()->flash('message-hours', 'Số giờ không được để giá trị âm!');
            return redirect()->back();
          }
        }
        $this->deleteEmployeeProject($data['id'], $data['day_work']);
        for ($i = 0; $i < count($data['projects']); $i++) {
          if ($this->checkPlan($data['id'], $data['projects'][$i], '1', $month)->exists()) {
            // echo 1;
            if ($this->checkEmployeeProject($data['id'], $data['projects'][$i], $data['day_work'])->exists()) {
              // echo 1.1;
              // $this->deleteEmployeeProject($data['id'], $data['day_work']);
              $this->addEmployeeProject($data['day_work'], $data['hours'][$i], '1', $data['id'], $data['projects'][$i]);
            } else {
              // echo 1.2;
              // $this->deleteEmployeeProject($data['id'], $data['day_work']);
              $this->addEmployeeProject($data['day_work'], $data['hours'][$i], '1', $data['id'], $data['projects'][$i]);
            }
          } else {
            // echo 'sub';
            if ($this->checkPlan($data['id'], $data['projects'][$i], '0', $month)->exists()) {
              // echo 2;
              if ($this->checkEmployeeProject($data['id'], $data['projects'][$i], $data['day_work'])->exists()) {
                // echo 2.1;
                // $this->deleteEmployeeProject($data['id'], $data['day_work']);
                $this->addEmployeeProject($data['day_work'], $data['hours'][$i], '0', $data['id'], $data['projects'][$i]);
              } else {
                // echo 2.2;
                $this->addEmployeeProject($data['day_work'], $data['hours'][$i], '0', $data['id'], $data['projects'][$i]);
              }
            } else {
              if (!$this->checkPlan($data['id'], $data['projects'][$i], '0', $month)->exists()) {
                DB::table('plan')->insert([
                  'status_plan' => '0',
                  'day_addEmp' => $data['day_work'],
                  'user_id' => $data['id'],
                  'project_id' => $data['projects'][$i],
                  'plan' => 0,
                ]);
                $this->addEmployeeProject($data['day_work'], $data['hours'][$i], '0', $data['id'], $data['projects'][$i]);
              }
            }
          }
        }
      }
      if (array_search(Null, $getProjectUnique) && count($getProjectUnique) > 0) {
        $request->session()->flash('message-project', 'Dự án không được để trống!');
        return back();
      }
      if (array_search(Null, $data['hours']) && count($data['hours']) > 0) {
        $request->session()->flash('message-hours', 'Số giờ không được để trống!');
        return back();
      }
      if ($getProject != $getProjectUnique  && count($getProject) > 0) {
        $request->session()->flash('message-project', 'Có dự án trùng nhau!');
        return back();
      }
      Auth::guard('tablet_users')->logout();
      return redirect()->route('tabletLoginGet');
    } catch (\Throwable $th) {
      abort(500);
    }
  }
  /***********************************************************************/
  public function checkPlan($id, $project, $status, $month)
  {
    $checkTablesPlan = DB::table('plan')
      ->whereMonth('day_addEmp', '=', $month[1])
      ->whereYear('day_addEmp', '=', $month[0])
      ->where([
        'user_id' => $id,
        'project_id' => $project,
        'status_plan' => $status,
      ]);
    return $checkTablesPlan;
  }
  public function checkEmployeeProject($id, $project, $day_work)
  {
    $checkDay = DB::table('employee_project')
      ->where([
        'user_id' => $id,
        'project_id' => $project,
        'day_work' => $day_work,
      ]);
    return $checkDay;
  }
  public function addEmployeeProject($day_work, $hours, $status, $id, $project)
  {
    DB::table('employee_project')->insert([
      'day_work' => $day_work,
      'working_hours' => $hours,
      'status_employee_project' => $status,
      'user_id' => $id,
      'project_id' => $project,
    ]);
  }
  public function deleteEmployeeProject($id, $day_work)
  {
    $check = DB::table('employee_project')->where([
      'user_id' => $id,
      'day_work' => $day_work,
    ]);
    if ($check->exists()) {
      $check->delete();
    }
  }
}
