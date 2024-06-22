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
