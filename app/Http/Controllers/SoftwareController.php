<?php

namespace App\Http\Controllers;

use App\Exports\SoftwareExport;
use App\Imports\SoftwareImport;
use App\Models\Computer;
use App\Models\Software;
use App\Models\SoftwareLicense;
use App\Services\SoftwareLicenseSync;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class SoftwareController extends Controller
{
    public function __construct(private SoftwareLicenseSync $licenseSync)
    {
    }

    public function index()
    {
        $software = Software::withCount('licenses')->latest()->paginate(10);
        return view('software.index', compact('software'));
    }

    public function create()
    {
        $computerOptions = $this->computerOptions();

        return view('software.create', compact('computerOptions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSoftware($request);
        $this->validateLicenses($validated['licenses'] ?? []);

        $validated['code'] = ($validated['code'] ?? null) ?: Software::generateCode();
        $licenses = $validated['licenses'] ?? [];

        DB::transaction(function () use ($validated, $licenses) {
            $software = Software::create(Arr::except($validated, ['licenses']));

            foreach ($licenses as $index => $license) {
                $software->licenses()->create([
                    'label' => $license['label'] ?? ('Lisensi '.($index + 1)),
                    'license_key' => $license['license_key'] ?? null,
                    'assigned_computer_id' => $license['assigned_computer_id'] ?? null,
                    'purchase_date' => $license['purchase_date'] ?? null,
                    'expiry_date' => $license['expiry_date'] ?? null,
                    'notes' => $license['notes'] ?? null,
                ]);
            }

            $this->licenseSync->syncComputers($software);
        });

        if ($request->input('action') === 'save_another') {
            return redirect()->route('software.create')
                ->with('success', 'Software berhasil ditambahkan. Silakan isi form untuk data berikutnya.');
        }

        return redirect()->route('software.index')->with('success', 'Software berhasil ditambahkan.');
    }

    public function show(Software $software)
    {
        $software->load(['licenses' => fn ($q) => $q->orderBy('created_at'), 'licenses.computer.laboratory', 'computers']);

        $canRevealKey = auth()->user()?->hasPermission('view-license-key') ?? false;

        return view('software.show', compact('software', 'canRevealKey'));
    }

    public function edit(Software $software)
    {
        $software->load(['licenses' => fn ($q) => $q->orderBy('created_at')]);
        $computerOptions = $this->computerOptions();

        return view('software.edit', compact('software', 'computerOptions'));
    }

    public function update(Request $request, Software $software)
    {
        $validated = $this->validateSoftware($request, $software);
        $this->validateLicenses($validated['licenses'] ?? []);

        $licenses = $validated['licenses'] ?? [];

        DB::transaction(function () use ($request, $software, $validated, $licenses) {
            $software->update(Arr::except($validated, ['licenses']));

            if (! $request->boolean('licenses_submitted') && ! $request->has('licenses')) {
                return;
            }

            $oldComputerIds = $software->licenses()
                ->whereNotNull('assigned_computer_id')
                ->pluck('assigned_computer_id')
                ->unique()
                ->all();

            $existingIds = $software->licenses()->pluck('id')->all();
            $submittedIds = [];

            foreach ($licenses as $index => $license) {
                $id = $license['id'] ?? null;

                if ($id !== null && ! in_array($id, $existingIds, true)) {
                    throw ValidationException::withMessages([
                        "licenses.$index.id" => 'Lisensi tidak valid untuk software ini.',
                    ]);
                }

                $attributes = [
                    'label' => $license['label'] ?? ('Lisensi '.($index + 1)),
                    'license_key' => $license['license_key'] ?? null,
                    'assigned_computer_id' => $license['assigned_computer_id'] ?? null,
                    'purchase_date' => $license['purchase_date'] ?? null,
                    'expiry_date' => $license['expiry_date'] ?? null,
                    'notes' => $license['notes'] ?? null,
                ];

                if ($id !== null) {
                    $software->licenses()->whereKey($id)->update($attributes);
                    $submittedIds[] = $id;
                } else {
                    $created = $software->licenses()->create($attributes);
                    $submittedIds[] = $created->id;
                }
            }

            $software->licenses()->whereNotIn('id', $submittedIds)->delete();

            $this->licenseSync->syncComputers($software, $oldComputerIds);
        });

        return redirect()->route('software.index')->with('success', 'Software berhasil diperbarui.');
    }

    public function destroy(Software $software)
    {
        $software->delete();

        return redirect()->route('software.index')->with('success', 'Software berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:software,id',
        ]);

        Software::whereIn('id', $request->ids)->delete();

        return redirect()->route('software.index')->with('success', count($request->ids) . ' software berhasil dihapus.');
    }

    public function revealLicenseKey(Software $software, SoftwareLicense $license)
    {
        abort_unless($license->software_id === $software->id, 404);

        return response()->json(['key' => $license->license_key]);
    }

    public function export()
    {
        $includeKeys = auth()->user()?->hasPermission('view-license-key') ?? false;

        return Excel::download(new SoftwareExport($includeKeys), 'software.xlsx');
    }

    public function template()
    {
        return Excel::download(new SoftwareExport(withData: false), 'template_software.xlsx');
    }

    public function import()
    {
        return view('software.import');
    }

    public function storeImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $import = new SoftwareImport();
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            return redirect()->route('software.import')
                ->with('import_error', 'Gagal mengimport: ' . $e->getMessage());
        }

        $message = "Berhasil mengimpor {$import->getImportedCount()} software dan {$import->getImportedLicenseCount()} lisensi";
        if ($errors = $import->getErrors()) {
            $message .= '. ' . count($errors) . ' baris gagal: ' . implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= ' dan ' . (count($errors) - 5) . ' lagi...';
            }
        }

        return redirect()->route('software.index')
            ->with('success', $message)
            ->with('import_errors', $import->getErrors());
    }

    private function validateSoftware(Request $request, ?Software $software = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:software,code'.($software ? ','.$software->id : ''),
            'version' => 'nullable|string|max:50',
            'license_type' => 'nullable|string|max:100',
            'provider' => 'nullable|string|max:255',
            'download_link' => 'nullable|url|max:255',
            'category' => 'required|string|max:255',
            'status' => 'required|in:Aktif,Expired,Trial,Non Aktif',
            'description' => 'nullable|string',
            'pic_name' => 'nullable|string|max:255',
            'pic_email' => 'nullable|email|max:255',
            'licenses' => 'nullable|array',
            'licenses.*.id' => 'nullable|uuid',
            'licenses.*.label' => 'nullable|string|max:255',
            'licenses.*.license_key' => 'nullable|string|max:255',
            'licenses.*.assigned_computer_id' => 'nullable|uuid|exists:computers,id',
            'licenses.*.purchase_date' => 'nullable|date',
            'licenses.*.expiry_date' => 'nullable|date',
            'licenses.*.notes' => 'nullable|string',
        ]);
    }

    private function validateLicenses(array $licenses): void
    {
        $assigned = [];

        foreach ($licenses as $index => $license) {
            $purchase = $license['purchase_date'] ?? null;
            $expiry = $license['expiry_date'] ?? null;

            if ($purchase && $expiry && strtotime($expiry) < strtotime($purchase)) {
                throw ValidationException::withMessages([
                    "licenses.$index.expiry_date" => 'Tanggal expired tidak boleh sebelum tanggal beli.',
                ]);
            }

            $computerId = $license['assigned_computer_id'] ?? null;
            if ($computerId) {
                if (isset($assigned[$computerId])) {
                    throw ValidationException::withMessages([
                        "licenses.$index.assigned_computer_id" => 'Satu PC hanya boleh mendapat satu lisensi untuk software ini.',
                    ]);
                }
                $assigned[$computerId] = true;
            }
        }
    }

    private function computerOptions(): array
    {
        return Computer::with('laboratory')
            ->orderBy('code')
            ->get()
            ->map(fn (Computer $computer) => [
                'id' => $computer->id,
                'label' => $computer->code.($computer->laboratory ? ' — '.$computer->laboratory->name : ''),
            ])
            ->values()
            ->all();
    }
}
