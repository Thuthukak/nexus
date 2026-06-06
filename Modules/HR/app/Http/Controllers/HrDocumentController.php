<?php

declare(strict_types=1);

namespace Modules\HR\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HR\app\Models\Employee;
use Modules\HR\app\Models\HrDocument;
use Modules\HR\app\Services\HrDocumentService;

class HrDocumentController extends Controller
{
    public function __construct(
        private HrDocumentService $service
    ) {}

    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|string|in:' . implode(',', array_keys(HrDocument::TYPES)),
            'file'        => 'required|file|max:20480|mimes:pdf,doc,docx,jpg,jpeg,png',
            'visibility'  => 'required|in:internal,customer',
            'expiry_date' => 'nullable|date|after:today',
            'customer_id' => 'nullable|uuid|exists:fin_customers,id',
            'notes'       => 'nullable|string|max:1000',
        ]);

        $this->service->store(
            $request->file('file'),
            array_merge($validated, ['employee_id' => $employee->id]),
            $request->user()->id,
        );

        return back()->with('toast', [
            'type'  => 'success',
            'title' => 'Document uploaded',
        ]);
    }

    public function download(Employee $employee, HrDocument $document)
    {
        abort_if($document->employee_id !== $employee->id, 403);
        return $this->service->download($document);
    }

    public function destroy(Employee $employee, HrDocument $document)
    {
        abort_if($document->employee_id !== $employee->id, 403);
        $this->service->delete($document);

        return back()->with('toast', [
            'type'  => 'success',
            'title' => 'Document deleted',
        ]);
    }
}
