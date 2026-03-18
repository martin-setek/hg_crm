<?php

namespace App\Http\Controllers;

use App\Models\Advisor;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with('advisor')->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->advisor_id) {
            $query->where('advisor_id', $request->advisor_id);
        }
        if ($request->region) {
            $query->where('region', $request->region);
        }
        if ($request->search) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('first_name', 'like', "%$s%")
                  ->orWhere('last_name', 'like', "%$s%")
                  ->orWhere('phone', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%");
            });
        }

        $leads    = $query->paginate(50)->withQueryString();
        $advisors = Advisor::active()->orderBy('name')->get();

        return view('pipeline.index', compact('leads', 'advisors'));
    }

    public function create()
    {
        $advisors = Advisor::active()->orderBy('name')->get();
        return view('pipeline.create', compact('advisors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'advisor_id'          => 'nullable|exists:advisors,id',
            'first_name'          => 'required|string|max:100',
            'last_name'           => 'required|string|max:100',
            'phone'               => 'nullable|string|max:20',
            'email'               => 'nullable|email|max:150',
            'region'              => 'nullable|string|max:50',
            'source'              => 'nullable|string|max:50',
            'type'                => 'nullable|string|max:20',
            'status'              => 'required|string|in:' . implode(',', array_keys(Lead::STATUSES)),
            'loan_amount'         => 'nullable|numeric|min:0',
            'loan_maturity_years' => 'nullable|integer|min:1|max:40',
            'property_value'      => 'nullable|numeric|min:0',
            'interest_rate'       => 'nullable|numeric|min:0|max:100',
            'commission_pct'      => 'nullable|numeric|min:0|max:1',
            'probability'         => 'nullable|numeric|min:0|max:1',
            'notes'               => 'nullable|string',
        ]);

        Lead::create($data);

        return redirect()->route('pipeline.index')
            ->with('success', 'Lead byl přidán.');
    }

    public function show(Lead $lead)
    {
        $lead->load('advisor');
        return view('pipeline.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        $advisors = Advisor::active()->orderBy('name')->get();
        return view('pipeline.edit', compact('lead', 'advisors'));
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'advisor_id'          => 'nullable|exists:advisors,id',
            'first_name'          => 'required|string|max:100',
            'last_name'           => 'required|string|max:100',
            'phone'               => 'nullable|string|max:20',
            'email'               => 'nullable|email|max:150',
            'region'              => 'nullable|string|max:50',
            'source'              => 'nullable|string|max:50',
            'type'                => 'nullable|string|max:20',
            'status'              => 'required|string|in:' . implode(',', array_keys(Lead::STATUSES)),
            'loan_amount'         => 'nullable|numeric|min:0',
            'loan_maturity_years' => 'nullable|integer|min:1|max:40',
            'property_value'      => 'nullable|numeric|min:0',
            'interest_rate'       => 'nullable|numeric|min:0|max:100',
            'commission_pct'      => 'nullable|numeric|min:0|max:1',
            'probability'         => 'nullable|numeric|min:0|max:1',
            'notes'               => 'nullable|string',
        ]);

        $lead->update($data);

        return redirect()->route('pipeline.show', $lead)
            ->with('success', 'Lead byl aktualizován.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return redirect()->route('pipeline.index')
            ->with('success', 'Lead byl smazán.');
    }

    /**
     * Quick status change via PATCH (from pipeline board).
     */
    public function updateStatus(Request $request, Lead $lead)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Lead::STATUSES)),
        ]);

        $lead->update(['status' => $request->status]);

        return response()->json([
            'ok'     => true,
            'status' => $lead->status,
            'label'  => $lead->status_label,
            'ev'     => $lead->ev_value,
        ]);
    }
}
