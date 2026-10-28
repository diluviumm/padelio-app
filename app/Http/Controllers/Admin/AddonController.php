<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\BookingAddon;
use Illuminate\Http\Request;

class AddonController extends Controller
{
    public function index()
    {
        $addons = Addon::orderBy('name')->get();

        return view('admin.addons', compact('addons'));
    }

    public function store(Request $request)
    {
        Addon::create($this->validated($request) + ['active' => true]);

        return back()->with('status', 'Peralatan ditambahkan.');
    }

    public function update(Addon $addon, Request $request)
    {
        $addon->update($this->validated($request));

        return back()->with('status', 'Peralatan diperbarui.');
    }

    public function destroy(Addon $addon)
    {
        if (BookingAddon::where('addon_id', $addon->id)->exists()) {
            return back()->withErrors(['addon' => 'Peralatan sudah dipakai booking. Nonaktifkan saja.']);
        }

        $addon->delete();

        return back()->with('status', 'Peralatan dihapus.');
    }

    public function stock(Addon $addon, Request $request)
    {
        $data = $request->validate(['delta' => ['required', 'integer', 'min:-100', 'max:100']]);

        $addon->update(['stock' => max(0, $addon->stock + (int) $data['delta'])]);

        return back()->with('status', 'Stok ' . $addon->name . ' menjadi ' . $addon->stock . '.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'price' => ['required', 'integer', 'min:0', 'max:10000000'],
            'unit' => ['required', 'string', 'max:20'],
            'deposit' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'stock' => ['required', 'integer', 'min:0', 'max:10000'],
            'icon' => ['required', 'string', 'max:30'],
        ]) + ['deposit' => (int) $request->input('deposit', 0)];
    }
}
