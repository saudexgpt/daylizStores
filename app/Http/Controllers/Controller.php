<?php

namespace App\Http\Controllers;

use App\ActivityLog;
use App\Customer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
use App\IssueTicket;
use App\Laravue\Models\Role;
use App\Laravue\Models\User;
use App\Location;
use App\Models\Invoice\Invoice;
use App\Models\Setting\Setting;
use App\Models\Setting\Tax;
use App\Models\Stock\Item;
use App\Models\Stock\ItemMedia;
use Notification;
use App\Notifications\AuditTrail;
use Intervention\Image\Facades\Image;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $user;

    public function uploadFile2(Request $request)
    {
        if ($request->file('file') != null && $request->file('file')->isValid()) {
            $this->validate($request, [
                'file' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
            ]);
            $image = $request->file('file');
            $imgFile = Image::make($image->getRealPath());
            $imgFile->resize(null, 240, function ($constraint) {
                $constraint->aspectRatio();
            });

            $name = time() . '.' . $image->getClientOriginalExtension();
            $folder = "storage/items";
            $avatar = $imgFile->storeAs($folder, $name, 'public');
            $link = '/' . $avatar;
            $media = new ItemMedia();
            $media->link = $link;
            $media->save();
            return response()->json(['media_id' => $media->id], 200);
        }
    }
    public function uploadFile(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);
        $image = $request->file('file');
        $name = randomcode() . time() . '.' . $image->getClientOriginalExtension();
        $folder = "/storage/items";
        $thumbnail_folder = $folder . "/thumbnails";
        $thumbnail_path = portalPulicPath($thumbnail_folder);

        // save thumbnail image
        $imgFile = Image::make($image->getRealPath());
        $imgFile->resize(250, null, function ($constraint) {
            $constraint->aspectRatio();
        })->save($thumbnail_path . '/' . $name);
        //keep original file
        $destinationPath = portalPulicPath($folder);
        $image->move($destinationPath, $name);

        $media = new ItemMedia();
        $media->link = $folder . '/' . $name;
        $media->thumbnail = $thumbnail_folder . '/' . $name;
        $media->save();

        return response()->json(['media_id' => $media->id], 200);
    }
    public function __construct()
    {
        // $this->middleware('guest')->except('logout');
        // $this->checkForNegativeTransitProduct();
        // $this->resetPartialInvoices();
    }

    public function setUser()
    {
        $this->user  = new UserResource(Auth::user());
    }

    public function getUser()
    {
        $this->setUser();

        return $this->user;
    }
    public function fetchCustomers()
    {
        $customers = Customer::paginate(10);
        return response()->json(compact('customers'), 200);
    }
    public function fetchNecessayParams()
    {
        $warehouses = [];
        $all_locations = Location::get();
        $items = Item::with(['price'])->orderBy('name')->get();
        $company_name = $this->settingValue('company_name');
        $company_contact = $this->settingValue('company_contact');
        $currency = $this->settingValue('currency');
        $account_details = $this->settingValue('account_details');
        $terms_and_conditions = $this->settingValue('terms_and_conditions');

        // $product_expiry_date_alert = $this->settingValue('product_expiry_date_alert_in_months');
        $order_statuses = ['Pending', 'On Transit', 'Delivered', 'Cancelled'];
        $genders = ['Men', 'Ladies', 'Boys', 'Girls'];
        $all_roles = Role::orderBy('name')->select('name')->get();
        //$customer_types = CustomerType::get();
        return response()->json([
            'params' => compact('all_locations', 'company_name', 'company_contact', 'warehouses', 'items', 'currency', 'genders', 'all_roles', 'order_statuses', 'account_details', 'terms_and_conditions')
        ]);
    }
    public function settingValue($key)
    {
        return Setting::where('key', $key)->first()->value;
    }
    public function generalSettings()
    {
        $settings = Setting::get();
        return response()->json(compact('settings'), 200);
    }
    public function nextReceiptNo($key_prefix = 'invoice')
    {
        $prefix = $this->settingValue($key_prefix . '_number_prefix');
        $no_of_digits = $this->settingValue($key_prefix . '_number_digit');
        $next_no = $this->settingValue($key_prefix . '_number_next');

        $digit_of_next_no = strlen($next_no);
        $unused_digit = $no_of_digits - $digit_of_next_no;
        $zeros = '';
        for ($i = 1; $i <= $unused_digit; $i++) {
            $zeros .= '0';
        }

        $invoice_no = $prefix . $zeros . $next_no;
        return $invoice_no;
    }

    public function incrementReceiptNo($key_prefix = 'invoice')
    {
        $next_no = $this->settingValue($key_prefix . '_number_next');
        $setting = Setting::where('key', $key_prefix . '_number_next')->first();
        $setting->value = $next_no + 1;
        $setting->save();
        return $setting;
    }

    public function logUserActivity($title, $description, $roles = [])
    {
        // $user = $this->getUser();
        // if ($role) {
        //     $role->notify(new AuditTrail($title, $description));
        // }
        // return $user->notify(new AuditTrail($title, $description));
        // send notification to admin at all times
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', '=', 'admin'); // this is the role id inside of this callback
        })->get();

        // if (in_array('assistant admin', $roles)) {
        //     $assistant_admin = User::whereHas('roles', function ($query) {
        //         $query->where('name', '=', 'assistant admin'); // this is the role id inside of this callback
        //     })->get();
        //     $users = $users->merge($assistant_admin);
        // }
        // if (in_array('warehouse manager', $roles)) {
        //     $warehouse_managers = User::whereHas('roles', function ($query) {
        //         $query->where('name', '=', 'warehouse manager'); // this is the role id inside of this callback
        //     })->get();
        //     $users = $users->merge($warehouse_managers);
        // }
        // if (in_array('stock officer', $roles)) {
        // $stock_officers = User::whereHas('roles', function ($query) use ($roles) {
        //     // $query->where('name', '=', 'stock officer'); // this is the role id inside of this callback
        //     $query->whereIn('name', $roles);
        // })->get();
        // $users = $users->merge($stock_officers);
        // }
        // if (in_array('warehouse auditor', $roles)) {
        //     $auditors = User::whereHas('roles', function ($query) {
        //         $query->where('name', '=', 'warehouse auditor'); // this is the role id inside of this callback
        //     })->get();
        //     $users = $users->merge($auditors);
        // }
        // var_dump($users);
        $notification = new AuditTrail($title, $description);
        return Notification::send($users->unique(), $notification);
        // $activity_log = new ActivityLog();
        // $activity_log->user_id = $user->id;
        // $activity_log->action = $action;
        // $activity_log->user_type = $user->roles[0]->name;
        // $activity_log->save();
    }
    public function getUniqueNo($prefix, $next_no)
    {
        $no_of_digits = 5;

        $digit_of_next_no = strlen($next_no);
        $unused_digit = $no_of_digits - $digit_of_next_no;
        $zeros = '';
        for ($i = 1; $i <= $unused_digit; $i++) {
            $zeros .= '0';
        }

        return $prefix . $zeros . $next_no;
    }
}
