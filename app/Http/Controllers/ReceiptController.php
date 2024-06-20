<?php

namespace App\Http\Controllers;

//import model product

use App\Models\Poly;
use App\Models\User;
use App\Models\Docter;
use App\Models\Receipt;
//import return type View
use Illuminate\View\View;

//import return type redirectResponse
use Illuminate\Http\Request;

//import Http Request
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;

class ReceiptController extends Controller
{
    public $receipt;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->receipt = new Receipt();
    }

    /**
     * dashboard
     *
     * @return void
     */
    public function dashboard()
    {
        // $polis = Poly::with('receiptsByStatusProcess')->get();
        // dd($polis);

        // $receiptsProses = collect(); // Membuat koleksi kosong untuk menampung receipt proses

        // foreach ($polis as $poli) {
        //     $receiptsProses = $receiptsProses->merge($poli->receipts->where('status', 'proses'));
        // }

        // $users = DB::table('Address')
        //     ->count();
        // dd($users);

        // Tanggal input
        $inDate = '2023-10-18';

        // // Query SQL
        // $query = "
        //     SELECT x.Kelompok,
        //         x.Tanggal,
        //         x.Jenis,
        //         x.Kelas,
        //         SUM(CASE
        //                 WHEN x.BusinessPartnerID = 2 THEN 1
        //                 ELSE 0
        //             END)       AS Jkn,
        //         SUM(CASE
        //                 WHEN x.BusinessPartnerID <> 2 THEN 1
        //                 ELSE 0
        //             END)       AS NonJkn,
        //         0              As Reg,
        //         0              As NonReg,
        //         COUNT(x.Kelas) AS Jumlah
        //     FROM (SELECT CASE
        //                     WHEN (a.ServiceUnitCode in ('35', '36', '37', '38', '40')) THEN 'KBY'
        //                     ELSE ('Ranap')
        //                     END as Kelompok,
        //                 CASE
        //                     WHEN a.ServiceUnitCode in ('37', '38', '39') THEN '37'
        //                     ELSE (a.ServiceUnitCode)
        //                     END as Jenis,
        //                 b.RoomId,
        //                 ? as Tanggal,
        //                 a.BedId,
        //                 CASE
        //                     WHEN (a.ServiceUnitCode = '20.02' and a.RoomCode like '%IS%') THEN 'ISO'
        //                     WHEN (a.ServiceUnitCode = '20.01' and a.RoomCode like '%IS%') THEN 'ISO TEK NEGATIF'
        //                     WHEN (a.ServiceUnitCode = '06' and a.RoomCode like '%IS%') THEN 'ISO ICU'
        //                     WHEN (a.ServiceUnitCode = '06' and a.ClassName = 'NON KELAS') THEN 'ICU'
        //                     WHEN a.ServiceUnitCode in ('37', '38', '39') THEN 'PATO-ISO-BARU'
        //                     WHEN a.ServiceUnitCode in ('35') THEN 'FISIO BARU'
        //                     WHEN a.ServiceUnitCode in ('36') THEN 'FISIO LAMA'
        //                     WHEN (a.ServiceUnitCode = '29' and a.isNewBorn = 1) THEN 'NICU'
        //                     WHEN (a.ServiceUnitCode = '29' and a.isNewBorn = 0) THEN 'PICU'
        //                     ELSE (a.ClassName)
        //                     END as Kelas,
        //                 a.BusinessPartnerID
        //         FROM bed b
        //                 LEFT JOIN vRegistration a on a.BedID = b.BedID
        //         where a.RegistrationDate <= ?
        //             and (a.DischargeDate > ? or a.DischargeDate is NULL)
        //             and a.DepartmentID = 'INPATIENT'
        //             and a.ServiceUnitCode <> '31'
        //             and a.GCRegistrationStatus <> 'X020^006') x
        //     GROUP BY Kelompok, Tanggal, Jenis, Kelas

        //     UNION ALL

        //     SELECT 'IGD'          as Kelompok,
        //         y.Tanggal,
        //         y.Jenis,
        //         y.Kelas,
        //         SUM(CASE
        //                 WHEN y.BusinessPartnerID = 2 THEN 1
        //                 ELSE 0
        //             END)       AS JKN,
        //         SUM(CASE
        //                 WHEN y.BusinessPartnerID <> 2 THEN 1
        //                 ELSE 0
        //             END)       AS NONJKN,
        //         0              As Reg,
        //         0              As NonReg,
        //         COUNT(y.Kelas) AS Jumlah
        //     FROM (SELECT SpecialtyName,
        //                 ServiceUnitName,
        //                 RegistrationDate as Tanggal,
        //                 CASE
        //                     WHEN ((GCAdmissionCondition = '0043^002' and SpecialtyName <> 'KANDUNGAN')) THEN 'BEDAH'
        //                     WHEN ((GCAdmissionCondition IS NULL or GCAdmissionCondition = '0043^001') and
        //                         SpecialtyName <> 'KANDUNGAN') THEN 'NON BEDAH'
        //                     WHEN (SpecialtyName = 'KANDUNGAN') THEN 'PONEK'
        //                     ELSE ('NON BEDAH')
        //                     END          as Kelas,
        //                 CASE
        //                     WHEN ((GCAdmissionCondition = '0043^002' and SpecialtyName <> 'KANDUNGAN')) THEN '01.01'
        //                     WHEN ((GCAdmissionCondition IS NULL or GCAdmissionCondition = '0043^001') and
        //                         SpecialtyName <> 'KANDUNGAN') THEN '01.03'
        //                     WHEN (SpecialtyName = 'KANDUNGAN') THEN '01.02'
        //                     ELSE ('01.03')
        //                     END          as Jenis,
        //                 BusinessPartnerID
        //         FROM vRegistration
        //         WHERE RegistrationDate = ?
        //             and ServiceUnitCode in ('01.01', '01.02')
        //             and GCRegistrationStatus <> 'X020^006') y
        //     GROUP BY y.Tanggal, y.Jenis, y.Kelas
        //     ";

        // // Menjalankan query dengan parameter binding
        // $results = DB::select($query, [$inDate, $inDate, $inDate, $inDate]);

        // // Output atau manipulasi hasil
        // foreach ($results as $result) {
        //     // Contoh akses data
        //     echo $result->Kelompok . ' - ' . $result->Tanggal . ' - ' . $result->Jenis . ' - ' . $result->Kelas . "\n";
        // }

        $query1 = DB::table(DB::raw("(
            SELECT
                CASE
                    WHEN (a.ServiceUnitCode in ('35', '36', '37', '38', '40')) THEN 'KBY'
                    ELSE 'Ranap'
                END as Kelompok,
                CASE
                    WHEN a.ServiceUnitCode in ('37', '38', '39') THEN '37'
                    ELSE a.ServiceUnitCode
                END as Jenis,
                b.RoomId,
                '$inDate' as Tanggal,
                a.BedId,
                CASE
                    WHEN (a.ServiceUnitCode = '20.02' and a.RoomCode like '%IS%') THEN 'ISO'
                    WHEN (a.ServiceUnitCode = '20.01' and a.RoomCode like '%IS%') THEN 'ISO TEK NEGATIF'
                    WHEN (a.ServiceUnitCode = '06' and a.RoomCode like '%IS%') THEN 'ISO ICU'
                    WHEN (a.ServiceUnitCode = '06' and a.ClassName = 'NON KELAS') THEN 'ICU'
                    WHEN a.ServiceUnitCode in ('37', '38', '39') THEN 'PATO-ISO-BARU'
                    WHEN a.ServiceUnitCode in ('35') THEN 'FISIO BARU'
                    WHEN a.ServiceUnitCode in ('36') THEN 'FISIO LAMA'
                    WHEN (a.ServiceUnitCode = '29' and a.isNewBorn = 1) THEN 'NICU'
                    WHEN (a.ServiceUnitCode = '29' and a.isNewBorn = 0) THEN 'PICU'
                    ELSE a.ClassName
                END as Kelas,
                a.BusinessPartnerID
            FROM bed b
            LEFT JOIN vRegistration a on a.BedID = b.BedID
            WHERE a.RegistrationDate <= '$inDate'
                AND (a.DischargeDate > '$inDate' OR a.DischargeDate is NULL)
                AND a.DepartmentID = 'INPATIENT'
                AND a.ServiceUnitCode <> '31'
                AND a.GCRegistrationStatus <> 'X020^006') AS x"))
            ->select('Kelompok', 'Tanggal', 'Jenis', 'Kelas')
            ->selectRaw("SUM(CASE WHEN BusinessPartnerID = 2 THEN 1 ELSE 0 END) AS JKN")
            ->selectRaw("SUM(CASE WHEN BusinessPartnerID <> 2 THEN 1 ELSE 0 END) AS NONJKN")
            ->selectRaw("0 AS Reg")
            ->selectRaw("0 AS NonReg")
            ->selectRaw("COUNT(Kelas) AS Jumlah")
            ->groupBy('Kelompok', 'Tanggal', 'Jenis', 'Kelas');

        $query2 = DB::table(DB::raw("(
            SELECT
                'IGD' as Kelompok,
                SpecialtyName,
                ServiceUnitName,
                RegistrationDate as Tanggal,
                CASE
                    WHEN ((GCAdmissionCondition = '0043^002' and SpecialtyName <> 'KANDUNGAN')) THEN 'BEDAH'
                    WHEN ((GCAdmissionCondition IS NULL or GCAdmissionCondition = '0043^001') and
                        SpecialtyName <> 'KANDUNGAN') THEN 'NON BEDAH'
                    WHEN (SpecialtyName = 'KANDUNGAN') THEN 'PONEK'
                    ELSE 'NON BEDAH'
                END as Kelas,
                CASE
                    WHEN ((GCAdmissionCondition = '0043^002' and SpecialtyName <> 'KANDUNGAN')) THEN '01.01'
                    WHEN ((GCAdmissionCondition IS NULL or GCAdmissionCondition = '0043^001') and
                        SpecialtyName <> 'KANDUNGAN') THEN '01.03'
                    WHEN (SpecialtyName = 'KANDUNGAN') THEN '01.02'
                    ELSE '01.03'
                END as Jenis,
                BusinessPartnerID
            FROM vRegistration
            WHERE RegistrationDate = '$inDate'
                AND ServiceUnitCode IN ('01.01', '01.02')
                AND GCRegistrationStatus <> 'X020^006') AS y"))
            ->select('Kelompok', 'Tanggal', 'Jenis', 'Kelas') // Remove one of the 'Jenis' columns
            ->selectRaw("SUM(CASE WHEN BusinessPartnerID = 2 THEN 1 ELSE 0 END) AS JKN")
            ->selectRaw("SUM(CASE WHEN BusinessPartnerID <> 2 THEN 1 ELSE 0 END) AS NONJKN")
            ->selectRaw("0 AS Reg")
            ->selectRaw("0 AS NonReg")
            ->selectRaw("COUNT(Kelas) AS Jumlah")
            ->groupBy('Kelompok', 'Tanggal', 'Jenis', 'Kelas');

        $results = $query1->union($query2)->get();

        // $results = $query1->get();
        // $results = $query2->get();

        foreach ($results as $result) {
            // Contoh akses data
            // echo gettype($result->Tanggal);
            echo $result->Kelompok . ' | ' . $result->Tanggal . ' | ' . $result->Jenis . ' | ' . $result->Kelas  . ' | '  . $result->JKN . ' | ' . $result->NONJKN . ' | ' . $result->Reg . ' | ' . $result->NonReg . ' | ' . $result->Jumlah . '<br>';
        }


        // //get all products
        // $polies = Poly::all();
        // $tittle = 'Sistem Antrian Obat';
        // // dd($polies);

        // //render view with receipts
        // return view('patient.dashboard', compact('polies', 'tittle'));
    }

    /**
     * index
     *
     * @return void
     */
    public function index(): View
    {
        //get all products
        $receipts = Receipt::latest()->paginate(10);

        //render view with receipts
        return view('pharmacy.receipt.index', compact('receipts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $polies = Poly::all();
        $users = User::all();
        $docters = Docter::all();

        return view('pharmacy.receipt.create', compact('polies', 'users', 'docters'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //validate form

        $request->validate([
            'code_order' => 'required|string|unique:receipts',
            'queue' => 'required|numeric',
            'patient_name' => 'required|string',
            'description_receipt' => 'required|string',
            'user_id' => 'required',
            'docter_id' => 'required',
            'status' => 'required',
            'poly_id' => 'required',
            'status' => 'required'
        ]);

        //create product
        $this->receipt->create([
            'code_order' => $request->code_order,
            'queue' => $request->queue,
            'patient_name' => $request->patient_name,
            'description_receipt' => $request->description_receipt,
            'user_id' => $request->user_id,
            'docter_id' => $request->docter_id,
            'status' => $request->status,
            'poly_id' => $request->poly_id,
            'status' => $request->status,
        ]);

        // $receipts = Receipt::with(['user', 'docter', 'poly'])->latest()->first();
        // $polies = Poly::with(['receipts', 'receiptsByStatusProcess', 'receiptsByStatusUndelivered'])->get();
        // event(new \App\Events\DashboardEvent($polies, $receipts));

        //redirect to index
        return redirect()->route('receipts.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Receipt $receipt)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Receipt $receipt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Receipt $receipt)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Receipt $receipt)
    {
        //
    }
}
