<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Setting;
use App\Models\Wheel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class GameController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function index()
    {
        return Wheel::all();
    }

    public function store(Request $request)
    {
        return Wheel::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $wheel = Wheel::findOrFail($id);
        $wheel->update($request->all());
        return $wheel;
    }

    public function destroy($id)
    {
        if ($id !== 'wheel-0') {
            Wheel::destroy($id);
            return response()->json(['message' => 'Deleted']);
        }
        return response()->json(['message' => 'Cannot delete default wheel'], 403);
    }

    public function getBackground()
    {
        $setting = Setting::first() ?? Setting::create(['background' => './img/background.jpg']);
        return $setting;
    }

    public function updateBackground(Request $request)
    {
        $setting = Setting::first() ?? new Setting();
        $setting->background = $request->background;
        $setting->save();
        return $setting;
    }

    public function getHistory()
    {
        return History::orderBy('spun_at', 'desc')->get();
    }

    public function storeHistory(Request $request)
    {
        $history = History::create([
            'result' => $request->input('result'),
            'spun_at' => now()
        ]);
        return response()->json($history);
    }

    public function deleteHistory()
    {
        History::truncate(); // Xóa toàn bộ bản ghi trong bảng history
        return response()->json(null, 204);
    }

    public function getCurrentWheel()
    {
        $setting = Setting::first() ?? Setting::create(['background' => './img/background.jpg', 'current_wheel_id' => 'wheel-0']);
        return response()->json(['current_wheel_id' => $setting->current_wheel_id]);
    }

    public function updateWheel(Request $request, $id)
    {
        $wheel = Wheel::findOrFail($id);
        $wheel->update($request->all());
        return response()->json($wheel); // Trả về wheel đã cập nhật
    }

    public function updateCurrentWheel(Request $request)
    {
        $setting = Setting::first() ?? new Setting();
        $setting->current_wheel_id = $request->input('current_wheel_id');
        $setting->save();
        return response()->json(['current_wheel_id' => $setting->current_wheel_id]);
    }

    public function getWheels()
    {
        $wheels = Wheel::all(); // Giả sử bạn có model Wheel
        $setting = Setting::first() ?? Setting::create(['background' => './img/background.jpg', 'current_wheel_id' => 'wheel-0']);
        return response()->json([
            'wheels' => $wheels,
            'current_wheel_id' => $setting->current_wheel_id
        ]);
    }
}
