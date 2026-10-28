<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\AuditService;
use App\Models\Court;
use App\Models\CourtMaintenance;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourtController extends Controller
{
    public function index(Request $request)
    {
        $courts = Court::withCount(['reviews'])->orderBy('name')->get();
        $date = $request->query('date', now()->toDateString());
        $maintenance = CourtMaintenance::where('date', $date)->get()->keyBy(fn ($m) => $m->court_id . '|' . $m->slot);

        return view('admin.courts', compact('courts', 'date', 'maintenance'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Court::create($data + ['active' => true]);

        return back()->with('status', 'Lapangan ditambahkan.');
    }

    public function update(Court $court, Request $request)
    {
        $court->update($this->validated($request));

        return back()->with('status', 'Lapangan diperbarui.');
    }

    public function destroy(Court $court, AuditService $audit)
    {
        if (Booking::where('court_id', $court->id)->whereIn('status', ['confirmed', 'hold'])->exists()) {
            return back()->withErrors(['court' => 'Lapangan masih punya booking aktif. Batalkan dulu.']);
        }

        $court->delete();
        $audit->log(request()->user(), 'court.delete', null, ['court' => $court->name]);

        return back()->with('status', 'Lapangan dihapus.');
    }

    public function toggleMaintenance(Court $court, Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'slot' => ['required', Rule::in(config('padelio.slots'))],
        ]);

        $existing = CourtMaintenance::where('court_id', $court->id)
            ->where('date', $data['date'])
            ->where('slot', $data['slot'])
            ->first();

        if ($existing) {
            $existing->delete();
            return back()->with('status', 'Maintenance dibuka untuk slot ' . $data['slot'] . '.');
        }

        CourtMaintenance::create([
            'court_id' => $court->id,
            'date' => $data['date'],
            'slot' => $data['slot'],
            'reason' => 'Maintenance',
        ]);

        return back()->with('status', 'Slot ' . $data['slot'] . ' ditandai maintenance.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'venue' => ['required', 'string', 'max:120'],
            'surface' => ['required', Rule::in(config('padelio.surfaces'))],
            'area' => ['required', Rule::in(config('padelio.areas'))],
            'lights' => ['nullable', 'boolean'],
            'rate_peak' => ['required', 'integer', 'min:0', 'max:10000000'],
            'rate_off' => ['required', 'integer', 'min:0', 'max:10000000'],
        ]) + ['lights' => $request->boolean('lights')];
    }
}
