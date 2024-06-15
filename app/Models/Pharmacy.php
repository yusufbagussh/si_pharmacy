<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pharmacy extends Model
{
    protected $connection = 'sqlsrv';

    use HasFactory;

    // $customerTypes = ['BPJS - Kemenkes', 'Pribadi', 'Rekanan', 'Karyawan - FASKES']; // Daftar CustomerType
    private $listLocationID = ['11', '12', '163']; // Daftar LocationID
    private $listCustomerTypeID = ['X004^500', 'X004^999', 'X004^100',  'X004^250']; // Daftar CustomerType

    function getBottomFiveNonRacikanRajal($startDate, $endDate, $location)
    {
        $queryNonRacikan =
            DB::table('PrescriptionOrderHd as poh')
            ->distinct()
            ->select(DB::raw("
            poh.DispensaryServiceUnitID,
            sud.ServiceUnitName as 'Dispensary',
            su.ServiceUnitName as 'Poli',
            CASE
                WHEN poh.SendOrderDateTime IS NOT NULL
                    THEN DATEDIFF(SECOND, poh.SendOrderDateTime, CURRENT_TIMESTAMP)
                WHEN pch.ProposedDate IS NOT NULL
                    THEN DATEDIFF(SECOND, pch.ProposedDate, CURRENT_TIMESTAMP)
                ELSE
                    NULL
            END AS 'DurationMinutes',
            r.RegistrationNo,
            p.MedicalNo,
            p.FullName as 'PatientName',
            pm.FullName as 'Nama Dokter',
            ua.FullName as 'Orderer',
            sc2.StandardCodeName as 'StatusOrder',
            poh.PrescriptionOrderID,
            poh.PrescriptionOrderNo,
            pch.TransactionID,
            pch.TransactionNo,
            poh.SendOrderDateTime,
            pch.ProposedDate as ProposedDateTime,
            ua2.FullName as 'User Propose',
            poh.ClosedDate as ClosedDateFarmasi,
            poh.ClosedTime as ClosedTimeFarmasi,
            bp.BusinessPartnerID,
            c.GCCustomerType,
            sc.StandardCodeName as 'Penjamin',
            bp.BusinessPartnerName,
            pch.TotalAmount,
            poh.CreatedDate,
            sc3.StandardCodeName as 'StatusTransaksi',
            poh.PrescriptionDate,
            pod.IsCompound,
            'NON RACIKAN' as 'JenisResep'
        "))
            ->join('PrescriptionOrderDt as pod', 'poh.PrescriptionOrderID', '=', 'pod.PrescriptionOrderID')

            ->leftJoin('PatientChargesHd as pch', function ($join) {
                $join->on('poh.PrescriptionOrderID', '=', 'pch.PrescriptionOrderID')
                    ->whereNotIn('pch.GCTransactionStatus', ['X121^999']);
            })
            ->join('ConsultVisit as cv', 'poh.VisitID', '=', 'cv.VisitID')
            ->join('Registration as r', 'cv.RegistrationID', '=', 'r.RegistrationID')
            ->join('Patient as p', 'r.MRN', '=', 'p.MRN')
            ->join('BusinessPartners as bp', 'r.BusinessPartnerID', '=', 'bp.BusinessPartnerID')
            ->join('Customer as c', 'bp.BusinessPartnerID', '=', 'c.BusinessPartnerID')
            ->join('StandardCode as sc', 'c.GCCustomerType', '=', 'sc.StandardCodeID')
            ->join('StandardCode as sc2', 'poh.GCTransactionStatus', '=', 'sc2.StandardCodeID')
            ->leftJoin('StandardCode as sc3', 'pch.GCTransactionStatus', '=', 'sc3.StandardCodeID')
            ->join('HealthcareServiceUnit as hsu', 'cv.HealthcareServiceUnitID', '=', 'hsu.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as su', 'hsu.ServiceUnitID', '=', 'su.ServiceUnitID')
            ->join('HealthcareServiceUnit as hsud', 'poh.DispensaryServiceUnitID', '=', 'hsud.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as sud', 'hsud.ServiceUnitID', '=', 'sud.ServiceUnitID')
            ->join('ParamedicMaster as pm', 'poh.ParamedicID', '=', 'pm.ParamedicID')
            ->join('UserAttribute as ua', 'poh.CreatedBy', '=', 'ua.UserID')
            ->leftJoin('UserAttribute as ua2', 'pch.ProposedBy', '=', 'ua2.UserID')
            ->whereBetween('poh.PrescriptionDate', [$startDate, $endDate])
            ->where('poh.DispensaryServiceUnitID', $location)
            ->whereNot('pch.TotalAmount', '=', '.00')
            ->whereNotIn('r.MRN', [10, 527556])
            ->whereNotIn('poh.GCTransactionStatus', ['X121^999'])
            ->whereNull('poh.ClosedDate')
            ->whereNotExists(function ($query) use ($startDate, $endDate, $location) {
                $query->select(DB::raw(1))
                    ->from('PrescriptionOrderHd as poh2')
                    ->join('PrescriptionOrderDt as pod', 'poh2.PrescriptionOrderID', '=', 'pod.PrescriptionOrderID')
                    ->leftJoin('PatientChargesHd as pch', function ($join) {
                        $join->on('poh2.PrescriptionOrderID', '=', 'pch.PrescriptionOrderID')
                            ->whereNotIn('pch.GCTransactionStatus', ['X121^999']);
                    })
                    ->join('ConsultVisit as cv', 'poh2.VisitID', '=', 'cv.VisitID')
                    ->join('Registration as r', 'cv.RegistrationID', '=', 'r.RegistrationID')
                    ->join('BusinessPartners as bp', 'r.BusinessPartnerID', '=', 'bp.BusinessPartnerID')
                    ->join('Customer as c', 'bp.BusinessPartnerID', '=', 'c.BusinessPartnerID')
                    ->whereBetween('poh.PrescriptionDate', [$startDate, $endDate])
                    ->where('poh2.DispensaryServiceUnitID', $location)
                    ->whereNotIn('poh2.GCTransactionStatus', ['X121^999'])
                    ->whereNot('pch.TotalAmount', '=', '.00')
                    ->whereNotIn('r.MRN', [10, 527556])
                    ->where('pod.IsCompound', 1)
                    ->whereRaw('poh2.PrescriptionOrderID = poh.PrescriptionOrderID')
                    ->whereNull('poh.ClosedDate');
            });

        $results = $queryNonRacikan->orderBy('DurationMinutes', 'desc')->limit(5)->get();

        return $results;
    }

    function getBottomFiveRacikanRajal($startDate, $endDate, $location)
    {
        $queryRacikan = DB::table('PrescriptionOrderHd as poh')
            ->distinct()
            ->select(DB::raw("poh.DispensaryServiceUnitID,
            sud.ServiceUnitName as 'Dispensary',
            su.ServiceUnitName as 'Poli',
            CASE
                WHEN poh.SendOrderDateTime IS NOT NULL
                    THEN DATEDIFF(SECOND, poh.SendOrderDateTime, CURRENT_TIMESTAMP)
                WHEN pch.ProposedDate IS NOT NULL
                    THEN DATEDIFF(SECOND, pch.ProposedDate, CURRENT_TIMESTAMP)
                ELSE
                    NULL
            END AS 'DurationMinutes',
            r.RegistrationNo,
            p.MedicalNo,
            p.FullName as 'PatientName',
            pm.FullName as 'Nama Dokter',
            ua.FullName as 'Orderer',
            sc2.StandardCodeName as 'StatusOrder',
            poh.PrescriptionOrderID,
            poh.PrescriptionOrderNo,
            pch.TransactionID,
            pch.TransactionNo,
            poh.SendOrderDateTime,
            pch.ProposedDate as ProposedDateTime,
            ua2.FullName as 'User Propose',
            poh.ClosedDate as ClosedDateFarmasi,
            poh.ClosedTime as ClosedTimeFarmasi,
            bp.BusinessPartnerID,
            c.GCCustomerType,
            sc.StandardCodeName as 'Penjamin',
            bp.BusinessPartnerName,
            pch.TotalAmount,
            poh.CreatedDate,
            sc3.StandardCodeName as 'StatusTransaksi',
            poh.PrescriptionDate,
            pod.IsCompound,
            'RACIKAN' as 'JenisResep'
        "))
            ->join('PrescriptionOrderDt as pod', 'poh.PrescriptionOrderID', '=', 'pod.PrescriptionOrderID')
            ->leftJoin('PatientChargesHd as pch', function ($join) {
                $join->on('poh.PrescriptionOrderID', '=', 'pch.PrescriptionOrderID')
                    ->whereNotIn('pch.GCTransactionStatus', ['X121^999']);
            })
            ->join('ConsultVisit as cv', 'poh.VisitID', '=', 'cv.VisitID')
            ->join('Registration as r', 'cv.RegistrationID', '=', 'r.RegistrationID')
            ->join('Patient as p', 'r.MRN', '=', 'p.MRN')
            ->join('BusinessPartners as bp', 'r.BusinessPartnerID', '=', 'bp.BusinessPartnerID')
            ->join('Customer as c', 'bp.BusinessPartnerID', '=', 'c.BusinessPartnerID')
            ->join('StandardCode as sc', 'c.GCCustomerType', '=', 'sc.StandardCodeID')
            ->join('StandardCode as sc2', 'poh.GCTransactionStatus', '=', 'sc2.StandardCodeID')
            ->leftJoin('StandardCode as sc3', 'pch.GCTransactionStatus', '=', 'sc3.StandardCodeID')
            ->join('HealthcareServiceUnit as hsu', 'cv.HealthcareServiceUnitID', '=', 'hsu.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as su', 'hsu.ServiceUnitID', '=', 'su.ServiceUnitID')
            ->join('HealthcareServiceUnit as hsud', 'poh.DispensaryServiceUnitID', '=', 'hsud.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as sud', 'hsud.ServiceUnitID', '=', 'sud.ServiceUnitID')
            ->join('ParamedicMaster as pm', 'poh.ParamedicID', '=', 'pm.ParamedicID')
            ->join('UserAttribute as ua', 'poh.CreatedBy', '=', 'ua.UserID')
            ->leftJoin('UserAttribute as ua2', 'pch.ProposedBy', '=', 'ua2.UserID')
            ->whereBetween('poh.PrescriptionDate', [$startDate, $endDate])
            ->where('poh.DispensaryServiceUnitID', $location)
            ->whereNot('pch.TotalAmount', '=', '.00')
            ->whereNotIn('r.MRN', [10, 527556])
            ->whereNotIn('poh.GCTransactionStatus', ['X121^999'])
            ->where('pod.IsCompound', 1)
            ->whereNull('poh.ClosedDate');

        $results = $queryRacikan->orderBy('DurationMinutes', 'desc')->limit(5)->get();
        return $results;
    }

    function getListOrderRajal(
        $startDate,
        $endDate,
        $customerType,
        $location,
        $statusOrder,
        $perPage,
        $search,
        $sortBy,
        $jenisOrder,
    ) {
        // $query = "
        //     DECLARE @FromDate DATE, @ToDate DATE, @Kelas AS VARCHAR(5), @Lokasi AS VARCHAR(5)
        //     SET @FromDate 	= ? ;
        //     SET @ToDate 	= ? ;
        //     SET @Lokasi		= '11';  -- GANTI DISINI SESUAI DGN FILTER Lokasi
        //     SET @Kelas		= 'RJ'   -- GANTI DISINI SESUAI DGN FILTER Kelas

        //     --- LocationID
        //         -- 11	FARMASI RAJAL
        //         -- 12	FARMASI RANAP
        //         -- 163	FARMASI IGD

        //    --- GCCustomerType
        //         -- X004^500 BPJS - Kemenkes
        //         -- X004^100 Rekanan
        //         -- X004^999 Pribadi
        //         -- X004^250 Karyawan - FASKES
        //         -- X004^201 Yayasan
        //         -- X004^200 Perusahaan

        //     -- Jika pasien BPJS tidak di propose Kasir untuk billingnya,
        //     -- di kerjakan terakhir

        //     SELECT
        //         -- TOP 100
        //         po.[LocationID]
        //         ,loc.LocationName
        //         ,vr.ServiceUnitName
        //             -- ,po.[PrescriptionOrderID]
        //         ,po.[PrescriptionOrderNo]
        //         ,po.[PrescriptionDate]
        //         ,po.[PrescriptionTime]
        //         ,vr.[RegistrationNo]
        //         ,vr.MedicalNo
        //         ,vr.PatientName
        //         ,vr.CustomerType
        //         ,vr.VisitID
        //         ,vr.GCRegistrationStatus
        //         ,sc4.[StandardCodeName] as 'Status Registrasi'
        //         ,po.[GCPrescriptionType]
        //         ,sc1.[StandardCodeName] as 'Jenis Resep'
        //         ,po.[ParamedicID]
        //         ,po.[ClassID]
        //         ,po.[DispensaryServiceUnitID]
        //         ,po.[GCTransactionStatus]
        //         ,sc3.[StandardCodeName] as 'Status Transaksi'
        //         ,po.[GCOrderStatus]
        //         ,sc3.[StandardCodeName] as 'Status Order'
        //         ,po.[SendOrderDateTime]
        //         ,po.[SendOrderBy]
        //         ,po.[StartDate] as 'StartDateFarmasi'
        //         ,po.[StartTime] as 'StartTimeFarmasi'
        //         --,po.[StartByName]
        //         ,po.[CompleteDate] as 'CompleteDateFarmasi'
        //         ,po.[CompleteTime] as 'CompleteTimeFarmasi'
        //         ,po.[CompleteByName]
        //         ,po.[ClosedDate] as 'ClosedDateFarmasi'
        //         ,po.[ClosedTime] as 'ClosedTimeFarmasi'
        //         ,po.[ClosedByName]
        //         ,po.[Remarks]
        //         ,po.[CreatedBy]
        //         ,po.[CreatedDate]
        //         ,po.[LastUpdatedBy]
        //         ,po.[LastUpdatedDate]
        //         ,po.[ProposedBy]
        //         ,us.[UserName] as 'Petugas'
        //     FROM [MS_RSDOSOBA].[dbo].[PrescriptionOrderHd] po
        //     LEFT JOIN [vRegistration]   vr	WITH (NOLOCK) ON po.VisitID = vr.VisitID and vr.GCRegistrationStatus <> 'X020^006'
        //     LEFT JOIN [Location]		loc WITH (NOLOCK) ON po.LocationID = loc.LocationID and loc.IsDeleted=0
        //     LEFT JOIN [StandardCode]	sc1	WITH (NOLOCK) ON po.[GCPrescriptionType]=sc1.StandardCodeID
        //     LEFT JOIN [StandardCode]	sc2	WITH (NOLOCK) ON po.[GCOrderStatus]=sc2.StandardCodeID
        //     LEFT JOIN [StandardCode]	sc3	WITH (NOLOCK) ON po.[GCTransactionStatus]=sc3.StandardCodeID
        //     LEFT JOIN [StandardCode]	sc4	WITH (NOLOCK) ON vr.[GCRegistrationStatus]=sc4.StandardCodeID
        //     LEFT JOIN [User]			us	WITH (NOLOCK) ON po.[ProposedBy]=us.UserID
        //     WHERE po.[PrescriptionDate] BETWEEN @FromDate AND @ToDate
        //     and po.[LocationID]=@Lokasi
        //     and po.[GCTransactionStatus]<>'X121^999'
        //     -- and po.[PrescriptionOrderNo] in ('OPM/20240520/00081','FAM/20240520/00008')
        //     -- and vr.[RegistrationNo] in ('OPM/20240520/00081')
        //     -- order by vr.ServiceUnitName,po.[PrescriptionDate],po.[PrescriptionTime],po.[DispensaryServiceUnitID] ,po.[LocationID]
        //     order by po.[CreatedDate] desc
        //     ";

        // $query = "
        //     SELECT
        //         po.[LocationID],
        //         loc.LocationName,
        //         vr.ServiceUnitName,
        //         po.[PrescriptionOrderNo],
        //         po.[PrescriptionDate],
        //         po.[PrescriptionTime],
        //         vr.[RegistrationNo],
        //         vr.MedicalNo,
        //         vr.PatientName,
        //         vr.CustomerType,
        //         vr.VisitID,
        //         vr.GCRegistrationStatus,
        //         sc4.[StandardCodeName] AS 'Status Registrasi',
        //         po.[GCPrescriptionType],
        //         sc1.[StandardCodeName] AS 'Jenis Resep',
        //         po.[ParamedicID],
        //         po.[ClassID],
        //         po.[DispensaryServiceUnitID],
        //         po.[GCTransactionStatus],
        //         sc3.[StandardCodeName] AS 'Status Transaksi',
        //         po.[GCOrderStatus],
        //         sc3.[StandardCodeName] AS 'Status Order',
        //         po.[SendOrderDateTime],
        //         po.[SendOrderBy],
        //         po.[StartDate] AS 'StartDateFarmasi',
        //         po.[StartTime] AS 'StartTimeFarmasi',
        //         po.[CompleteDate] AS 'CompleteDateFarmasi',
        //         po.[CompleteTime] AS 'CompleteTimeFarmasi',
        //         po.[CompleteByName],
        //         po.[ClosedDate] AS 'ClosedDateFarmasi',
        //         po.[ClosedTime] AS 'ClosedTimeFarmasi',
        //         po.[ClosedByName],
        //         po.[Remarks],
        //         po.[CreatedBy],
        //         po.[CreatedDate],
        //         po.[LastUpdatedBy],
        //         po.[LastUpdatedDate],
        //         po.[ProposedBy],
        //         us.[UserName] AS 'Petugas'
        //     FROM [MS_RSDOSOBA].[dbo].[PrescriptionOrderHd] po
        //     LEFT JOIN [vRegistration] vr ON po.VisitID = vr.VisitID AND vr.GCRegistrationStatus <> 'X020^006'
        //     LEFT JOIN [Location] loc ON po.LocationID = loc.LocationID AND loc.IsDeleted = 0
        //     LEFT JOIN [StandardCode] sc1 ON po.[GCPrescriptionType] = sc1.StandardCodeID
        //     LEFT JOIN [StandardCode] sc2 ON po.[GCOrderStatus] = sc2.StandardCodeID
        //     LEFT JOIN [StandardCode] sc3 ON po.[GCTransactionStatus] = sc3.StandardCodeID
        //     LEFT JOIN [StandardCode] sc4 ON vr.[GCRegistrationStatus] = sc4.StandardCodeID
        //     LEFT JOIN [User] us ON po.[ProposedBy] = us.UserID
        //     WHERE po.[PrescriptionDate] BETWEEN ? AND ?
        //     AND vr.CustomerType = ?
        //     AND po.[LocationID] = '11' -- GANTI DISINI SESUAI DGN FILTER Lokasi
        //     AND po.[GCTransactionStatus] <> 'X121^999'
        //     ORDER BY po.[CreatedDate] DESC
        // ";

        // $results = DB::select($query, [$startDate, $endDate, $customerType]);

        // $query = "
        // DECLARE @tglAwal DATE='20240501',
        // @tglAkhir DATE='20240531',
        // @tglOrder DATE='20240604'

        // --RACIKAN
        // SELECT DISTINCT
        //     poh.DispensaryServiceUnitID, -- id farmasi = id table healthcare service unit
        //     sud.ServiceUnitName 'Dispensary', --nama farmasi
        //     su.ServiceUnitName 'Poli', --poli
        //     r.RegistrationNo, --no registrasi px
        //     p.MedicalNo, --no rm px
        //     p.FullName 'PatientName', --nama px
        //     --pm.ParamedicID, --id dr yg meresepkan
        //     pm.FullName 'Nama Dokter', --nama dr yg meresepkan
        //     --poh.CreatedBy, --id user yg membuat order
        //     ua.FullName 'Orderer', --nama user yg membuat order
        //     sc2.StandardCodeName 'OrderStatus', --status order
        //     poh.PrescriptionOrderID, --id order resep
        //     poh.PrescriptionOrderNo, --no order resep
        //     pch.TransactionID, --id transaksi resep
        //     pch.TransactionNo, --no transaksi resep
        //     poh.SendOrderDateTime, --tgl&jam order resep dikirim
        //     pch.ProposedDate as ProposedDateTime, --tgl&jam farmasi mulai menyiapkan obat
        //     --pch.ProposedBy, --id user yg propose
        //     ua2.FullName 'User Propose', --nama user yg propose
        //     poh.ClosedDate as ClosedDateFarmasi, --tgl obat siap diserahkan (farmasi klik sudah diserahkan di medinfras)
        //     poh.ClosedTime as ClosedTimeFarmasi, --tgl obat siap diserahkan (farmasi klik sudah diserahkan di medinfras)
        //     bp.BusinessPartnerID, --id penjamin
        //     c.GCCustomerType, --standard code id tipe customer
        //     sc.StandardCodeName, --nama tipe customer
        //     bp.BusinessPartnerName, --nama penjamin
        //     pch.TotalAmount, --nominal resep, jml 0 artinya tidak diambil pasien
        //     sc3.StandardCodeName 'StatusTransaksi', --status transaksi
        //     'RACIKAN' AS 'JenisResep'
        // FROM PrescriptionOrderHd poh
        //     JOIN PrescriptionOrderDt pod ON poh.PrescriptionOrderID = pod.PrescriptionOrderID
        //     FULL OUTER JOIN PatientChargesHd pch ON poh.PrescriptionOrderID = pch.PrescriptionOrderID AND pch.GCTransactionStatus NOT IN ('X121^999') --transaksi tdk divoid
        //     JOIN ConsultVisit cv ON poh.VisitID =  cv.VisitID
        //     JOIN Registration r ON cv.RegistrationID = r.RegistrationID
        //     JOIN Patient p ON r.MRN = p.MRN
        //     JOIN BusinessPartners bp ON r.BusinessPartnerID = bp.BusinessPartnerID
        //     JOIN Customer c ON bp.BusinessPartnerID = c.BusinessPartnerID
        //     JOIN StandardCode sc ON c.GCCustomerType = sc.StandardCodeID
        //     JOIN StandardCode sc2 ON poh.GCTransactionStatus = sc2.StandardCodeID --status order
        //     FULL OUTER JOIN StandardCode sc3 ON pch.GCTransactionStatus = sc3.StandardCodeID --status transaksi
        //     JOIN HealthcareServiceUnit hsu ON cv.HealthcareServiceUnitID = hsu.HealthcareServiceUnitID
        //     JOIN ServiceUnitMaster su ON hsu.ServiceUnitID = su.ServiceUnitID
        //     JOIN HealthcareServiceUnit hsud ON poh.DispensaryServiceUnitID = hsud.HealthcareServiceUnitID
        //     JOIN ServiceUnitMaster sud ON hsud.ServiceUnitID = sud.ServiceUnitID
        //     JOIN ParamedicMaster pm ON poh.ParamedicID = pm.ParamedicID
        //     JOIN UserAttribute ua ON poh.CreatedBy = ua.UserID --user yg buat order
        //     FULL OUTER JOIN UserAttribute ua2 ON pch.ProposedBy = ua2.UserID --user yg propose transaksi
        // WHERE poh.PrescriptionDate = @tglOrder  -- filter tgl order --> poh.PrescriptionDate BETWEEN @tglAwal AND @tglAkhir
        //     AND poh.DispensaryServiceUnitID = 100 --filter lokasi farmasi (ambil dari DispensaryServiceUnitID/HealthcareServiceUnitID)  100 = FARMASI RAWAT JALAN, 101 = FARMASI RAWAT INAP, 133 = FARMASI RAWAT INAP NON UDD, 166 = FARMASI IGD
        //     AND c.GCCustomerType = 'X004^999' --filter tipe penjamin X004^250 = Karyawan - FASKES, X004^100 = Rekanan, X004^999 = Pribadi, X004^300 = Pemerintah, X004^500 = BPJS - Kemenkes, X004^400 = Rumah Sakit, X004^200 = Perusahaan, X004^201 = Yayasan, X004^251 = Karyawan - PTGJ
        //     AND poh.GCTransactionStatus NOT IN ('X121^999') --order tdk divoid
        //     AND pod.IsCompound = 1

        // UNION ALL

        // --NON RACIKAN
        // SELECT DISTINCT
        //     poh.DispensaryServiceUnitID, -- id farmasi = id table healthcare service unit
        //     sud.ServiceUnitName 'Dispensary', --nama farmasi
        //     su.ServiceUnitName 'Poli', --poli
        //     r.RegistrationNo, --no registrasi px
        //     p.MedicalNo, --no rm px
        //     p.FullName 'PatientName', --nama px
        //     --pm.ParamedicID, --id dr yg meresepkan
        //     pm.FullName 'Nama Dokter', --nama dr yg meresepkan
        //     --poh.CreatedBy, --id user yg membuat order
        //     ua.FullName 'User Buat', --nama user yg membuat order
        //     sc2.StandardCodeName 'OrderStatus', --status order
        //     poh.PrescriptionOrderID, --id order resep
        //     poh.PrescriptionOrderNo, --no order resep
        //     pch.TransactionID, --id transaksi resep
        //     pch.TransactionNo, --no transaksi resep
        //     poh.SendOrderDateTime, --tgl&jam order resep dikirim
        //     pch.ProposedDate as ProposedDateTime, --tgl&jam farmasi mulai menyiapkan obat
        //     --pch.ProposedBy, --id user yg propose
        //     ua2.FullName 'User Propose', --nama user yg propose
        //     poh.ClosedDate as ClosedDateFarmasi, --tgl obat siap diserahkan (farmasi klik sudah diserahkan di medinfras)
        //     poh.ClosedTime as ClosedTimeFarmasi, --tgl obat siap diserahkan (farmasi klik sudah diserahkan di medinfras)
        //     bp.BusinessPartnerID, --id penjamin
        //     c.GCCustomerType, --standard code id tipe customer
        //     sc.StandardCodeName, --nama tipe customer
        //     bp.BusinessPartnerName, --nama penjamin
        //     pch.TotalAmount, --nominal resep, jml 0 artinya tidak diambil pasien
        //     sc3.StandardCodeName 'StatusTransaksi', --status transaksi
        //     'NON RACIKAN' AS 'JenisResep'
        // FROM PrescriptionOrderHd poh
        //     ----JOIN PrescriptionOrderDt pod ON poh.PrescriptionOrderID = pod.PrescriptionOrderID
        //     FULL OUTER JOIN PatientChargesHd pch ON poh.PrescriptionOrderID = pch.PrescriptionOrderID AND pch.GCTransactionStatus NOT IN ('X121^999') --transaksi tdk divoid
        //     JOIN ConsultVisit cv ON poh.VisitID =  cv.VisitID
        //     JOIN Registration r ON cv.RegistrationID = r.RegistrationID
        //     JOIN Patient p ON r.MRN = p.MRN
        //     JOIN BusinessPartners bp ON r.BusinessPartnerID = bp.BusinessPartnerID
        //     JOIN Customer c ON bp.BusinessPartnerID = c.BusinessPartnerID
        //     JOIN StandardCode sc ON c.GCCustomerType = sc.StandardCodeID
        //     JOIN StandardCode sc2 ON poh.GCTransactionStatus = sc2.StandardCodeID --status order
        //     FULL OUTER JOIN  StandardCode sc3 ON pch.GCTransactionStatus = sc3.StandardCodeID --status transaksi
        //     JOIN HealthcareServiceUnit hsu ON cv.HealthcareServiceUnitID = hsu.HealthcareServiceUnitID
        //     JOIN ServiceUnitMaster su ON hsu.ServiceUnitID = su.ServiceUnitID
        //     JOIN HealthcareServiceUnit hsud ON poh.DispensaryServiceUnitID = hsud.HealthcareServiceUnitID
        //     JOIN ServiceUnitMaster sud ON hsud.ServiceUnitID = sud.ServiceUnitID
        //     JOIN ParamedicMaster pm ON poh.ParamedicID = pm.ParamedicID
        //     JOIN UserAttribute ua ON poh.CreatedBy = ua.UserID --user yg buat order
        //     FULL OUTER JOIN UserAttribute ua2 ON pch.ProposedBy = ua2.UserID --user yg propose transaksi
        // WHERE poh.PrescriptionDate = @tglOrder  -- filter tgl order --> poh.PrescriptionDate BETWEEN @tglAwal AND @tglAkhir
        //     AND poh.DispensaryServiceUnitID = 100 --filter lokasi farmasi (ambil dari DispensaryServiceUnitID/HealthcareServiceUnitID)  100 = FARMASI RAWAT JALAN, 101 = FARMASI RAWAT INAP, 133 = FARMASI RAWAT INAP NON UDD, 166 = FARMASI IGD
        //     AND c.GCCustomerType = 'X004^999' --filter tipe penjamin X004^250 = Karyawan - FASKES, X004^100 = Rekanan, X004^999 = Pribadi, X004^300 = Pemerintah, X004^500 = BPJS - Kemenkes, X004^400 = Rumah Sakit, X004^200 = Perusahaan, X004^201 = Yayasan, X004^251 = Karyawan - PTGJ
        //     AND poh.GCTransactionStatus NOT IN ('X121^999') --order tdk divoid
        //     AND poh.PrescriptionOrderID NOT IN (SELECT DISTINCT poh.PrescriptionOrderID FROM PrescriptionOrderHd poh
        //         JOIN PrescriptionOrderDt pod ON poh.PrescriptionOrderID = pod.PrescriptionOrderID
        //         FULL OUTER JOIN PatientChargesHd pch ON poh.PrescriptionOrderID = pch.PrescriptionOrderID AND pch.GCTransactionStatus NOT IN ('X121^999') --transaksi tdk divoid
        //         JOIN ConsultVisit cv ON poh.VisitID =  cv.VisitID
        //         JOIN Registration r ON cv.RegistrationID = r.RegistrationID
        //         JOIN BusinessPartners bp ON r.BusinessPartnerID = bp.BusinessPartnerID
        //         JOIN Customer c ON bp.BusinessPartnerID = c.BusinessPartnerID
        //         WHERE poh.PrescriptionDate = @tglOrder  -- filter tgl order --> poh.PrescriptionDate BETWEEN @tglAwal AND @tglAkhir
        //         AND poh.DispensaryServiceUnitID = 100
        //         AND c.GCCustomerType = 'X004^999'
        //         AND poh.GCTransactionStatus NOT IN ('X121^999')
        //         AND pod.IsCompound = 1)
        // ORDER BY poh.SendOrderDateTime
        // ";

        // $results = DB::select($query);

        // $dispensaryServiceUnitID = 100; // contoh ID, ini bisa diganti dengan input dari pengguna
        // $customerType = 'X004^500'; // contoh tipe penjamin, ini bisa diganti dengan input dari pengguna
        $queryRacikan = DB::table('PrescriptionOrderHd as poh')
            ->select(DB::raw("
            DISTINCT poh.DispensaryServiceUnitID,
            sud.ServiceUnitName as 'Dispensary',
            su.ServiceUnitName as 'Poli',
            r.RegistrationNo,
            p.MedicalNo,
            p.FullName as 'PatientName',
            pm.FullName as 'Nama Dokter',
            ua.FullName as 'Orderer',
            sc2.StandardCodeName as 'StatusOrder',
            poh.PrescriptionOrderID,
            poh.PrescriptionOrderNo,
            pch.TransactionID,
            pch.TransactionNo,
            poh.SendOrderDateTime,
            pch.ProposedDate as ProposedDateTime,
            ua2.FullName as 'User Propose',
            poh.ClosedDate as ClosedDateFarmasi,
            poh.ClosedTime as ClosedTimeFarmasi,
            bp.BusinessPartnerID,
            c.GCCustomerType,
            sc.StandardCodeName as 'Penjamin',
            bp.BusinessPartnerName,
            pch.TotalAmount,
            poh.CreatedDate,
            sc3.StandardCodeName as 'StatusTransaksi',
            poh.PrescriptionDate,
            pod.IsCompound,
            'RACIKAN' as 'JenisResep'
        "))
            ->join('PrescriptionOrderDt as pod', 'poh.PrescriptionOrderID', '=', 'pod.PrescriptionOrderID')
            ->leftJoin('PatientChargesHd as pch', function ($join) {
                $join->on('poh.PrescriptionOrderID', '=', 'pch.PrescriptionOrderID')
                    ->whereNotIn('pch.GCTransactionStatus', ['X121^999']);
            })
            ->join('ConsultVisit as cv', 'poh.VisitID', '=', 'cv.VisitID')
            ->join('Registration as r', 'cv.RegistrationID', '=', 'r.RegistrationID')
            ->join('Patient as p', 'r.MRN', '=', 'p.MRN')
            ->join('BusinessPartners as bp', 'r.BusinessPartnerID', '=', 'bp.BusinessPartnerID')
            ->join('Customer as c', 'bp.BusinessPartnerID', '=', 'c.BusinessPartnerID')
            ->join('StandardCode as sc', function ($join) {
                $join->on('c.GCCustomerType', '=', 'sc.StandardCodeID')
                    ->whereNotIn('c.GCCustomerType', ['X004^250', 'X004^201', 'X004^400']);
            })
            ->join('StandardCode as sc2', 'poh.GCTransactionStatus', '=', 'sc2.StandardCodeID')
            ->leftJoin('StandardCode as sc3', 'pch.GCTransactionStatus', '=', 'sc3.StandardCodeID')
            ->join('HealthcareServiceUnit as hsu', 'cv.HealthcareServiceUnitID', '=', 'hsu.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as su', 'hsu.ServiceUnitID', '=', 'su.ServiceUnitID')
            ->join('HealthcareServiceUnit as hsud', 'poh.DispensaryServiceUnitID', '=', 'hsud.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as sud', 'hsud.ServiceUnitID', '=', 'sud.ServiceUnitID')
            ->join('ParamedicMaster as pm', 'poh.ParamedicID', '=', 'pm.ParamedicID')
            ->join('UserAttribute as ua', 'poh.CreatedBy', '=', 'ua.UserID')
            ->leftJoin('UserAttribute as ua2', 'pch.ProposedBy', '=', 'ua2.UserID')
            ->whereBetween('poh.PrescriptionDate', [$startDate, $endDate])
            ->where('poh.DispensaryServiceUnitID', $location)
            ->whereNot('pch.TotalAmount', '=', '.00')
            ->whereNotIn('r.MRN', [10, 527556])
            ->whereNotIn('poh.GCTransactionStatus', ['X121^999']);
        // ->where('pod.IsCompound', 1);
        // ->whereNot('sc3.StandardCodeName', '=', 'CLOSED')
        // ->whereNull('poh.ClosedDate')

        if ($statusOrder !== 'all') {
            if ($statusOrder === '2') {
                $queryRacikan
                    // ->whereNot('sc3.StandardCodeName', '=', 'CLOSED')
                    // ->WhereNot('sc2.StandardCodeName', '=', 'PROCESSED')
                    ->whereNull('poh.ClosedDate');
            } else { //selesai
                $queryRacikan
                    ->where(function ($queryRacikan) {
                        $queryRacikan
                            ->WhereNotNull('poh.ClosedDate');
                        // ->orWhere('sc3.StandardCodeName', '=', 'CLOSED')
                        // ->orWhere('sc2.StandardCodeName', '=', 'PROCESSED');
                    });
            }
        }

        if ($customerType === '1') {
            $queryRacikan->where('c.GCCustomerType', '=', 'X004^500');
        } else if ($customerType === '2') {
            $queryRacikan->whereIn('c.GCCustomerType', ['X004^999', 'X004^251', 'X004^300']);
        } else if ($customerType === '3') {
            $queryRacikan->whereIn('c.GCCustomerType', ['X004^100', 'X004^200']);
        } else if ($customerType === '4') {
            $queryRacikan->whereIn('c.GCCustomerType', ['X004^250', 'X004^400', 'X004^201']);
        }

        if (!empty($search)) {
            $queryRacikan->where(function ($query) use ($search) {
                $query
                    ->where('p.FullName', 'LIKE', "%{$search}%")
                    ->orWhere('p.MedicalNo', 'LIKE', "%{$search}%")
                    ->orWhere('sc.StandardCodeName', 'LIKE', "%{$search}%");
            });
        }

        $queryRacikan->where('pod.IsCompound', 1);

        // Query untuk resep non-racikan
        $queryNonRacikan = DB::table('PrescriptionOrderHd as poh')
            ->select(DB::raw("
            DISTINCT poh.DispensaryServiceUnitID,
            sud.ServiceUnitName as 'Dispensary',
            su.ServiceUnitName as 'Poli',
            r.RegistrationNo,
            p.MedicalNo,
            p.FullName as 'PatientName',
            pm.FullName as 'Nama Dokter',
            ua.FullName as 'Orderer',
            sc2.StandardCodeName as 'StatusOrder',
            poh.PrescriptionOrderID,
            poh.PrescriptionOrderNo,
            pch.TransactionID,
            pch.TransactionNo,
            poh.SendOrderDateTime,
            pch.ProposedDate as ProposedDateTime,
            ua2.FullName as 'User Propose',
            poh.ClosedDate as ClosedDateFarmasi,
            poh.ClosedTime as ClosedTimeFarmasi,
            bp.BusinessPartnerID,
            c.GCCustomerType,
            sc.StandardCodeName as 'Penjamin',
            bp.BusinessPartnerName,
            pch.TotalAmount,
            poh.CreatedDate,
            sc3.StandardCodeName as 'StatusTransaksi',
            poh.PrescriptionDate,
            pod.IsCompound,
            'NON RACIKAN' as 'JenisResep'
        "))
            ->join('PrescriptionOrderDt as pod', 'poh.PrescriptionOrderID', '=', 'pod.PrescriptionOrderID')

            ->leftJoin('PatientChargesHd as pch', function ($join) {
                $join->on('poh.PrescriptionOrderID', '=', 'pch.PrescriptionOrderID')
                    ->whereNotIn('pch.GCTransactionStatus', ['X121^999']);
            })
            ->join('ConsultVisit as cv', 'poh.VisitID', '=', 'cv.VisitID')
            ->join('Registration as r', 'cv.RegistrationID', '=', 'r.RegistrationID')
            ->join('Patient as p', 'r.MRN', '=', 'p.MRN')
            ->join('BusinessPartners as bp', 'r.BusinessPartnerID', '=', 'bp.BusinessPartnerID')
            ->join('Customer as c', 'bp.BusinessPartnerID', '=', 'c.BusinessPartnerID')
            ->join('StandardCode as sc', function ($join) {
                $join->on('c.GCCustomerType', '=', 'sc.StandardCodeID')
                    ->whereNotIn('c.GCCustomerType', ['X004^250', 'X004^201', 'X004^400']);
            })
            ->join('StandardCode as sc2', 'poh.GCTransactionStatus', '=', 'sc2.StandardCodeID')
            ->leftJoin('StandardCode as sc3', 'pch.GCTransactionStatus', '=', 'sc3.StandardCodeID')
            ->join('HealthcareServiceUnit as hsu', 'cv.HealthcareServiceUnitID', '=', 'hsu.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as su', 'hsu.ServiceUnitID', '=', 'su.ServiceUnitID')
            ->join('HealthcareServiceUnit as hsud', 'poh.DispensaryServiceUnitID', '=', 'hsud.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as sud', 'hsud.ServiceUnitID', '=', 'sud.ServiceUnitID')
            ->join('ParamedicMaster as pm', 'poh.ParamedicID', '=', 'pm.ParamedicID')
            ->join('UserAttribute as ua', 'poh.CreatedBy', '=', 'ua.UserID')
            ->leftJoin('UserAttribute as ua2', 'pch.ProposedBy', '=', 'ua2.UserID')
            ->whereBetween('poh.PrescriptionDate', [$startDate, $endDate])
            ->where('poh.DispensaryServiceUnitID', $location)
            ->whereNot('pch.TotalAmount', '=', '.00')
            ->whereNotIn('r.MRN', [10, 527556])
            // ->whereNot('sc3.StandardCodeName', '=', 'CLOSED')
            // ->whereNull('poh.ClosedDate')
            ->whereNotIn('poh.GCTransactionStatus', ['X121^999'])
            ->whereNotExists(function ($query) use ($startDate, $endDate, $location, $customerType, $statusOrder) {
                $query->select(DB::raw(1))
                    ->from('PrescriptionOrderHd as poh2')
                    ->join('PrescriptionOrderDt as pod', 'poh2.PrescriptionOrderID', '=', 'pod.PrescriptionOrderID')
                    ->leftJoin('PatientChargesHd as pch', function ($join) {
                        $join->on('poh2.PrescriptionOrderID', '=', 'pch.PrescriptionOrderID')
                            ->whereNotIn('pch.GCTransactionStatus', ['X121^999']);
                    })
                    ->join('ConsultVisit as cv', 'poh2.VisitID', '=', 'cv.VisitID')
                    ->join('Registration as r', 'cv.RegistrationID', '=', 'r.RegistrationID')
                    ->join('BusinessPartners as bp', 'r.BusinessPartnerID', '=', 'bp.BusinessPartnerID')
                    ->join('Customer as c', 'bp.BusinessPartnerID', '=', 'c.BusinessPartnerID')
                    ->whereBetween('poh.PrescriptionDate', [$startDate, $endDate])
                    ->where('poh2.DispensaryServiceUnitID', $location)
                    ->whereNotIn('poh2.GCTransactionStatus', ['X121^999'])
                    ->whereNot('pch.TotalAmount', '=', '.00')
                    ->whereNotIn('r.MRN', [10, 527556])
                    ->where('pod.IsCompound', 1)
                    // ->whereNot('sc3.StandardCodeName', '=', 'CLOSED')
                    // ->whereNull('poh.ClosedDate')
                    ->whereRaw('poh2.PrescriptionOrderID = poh.PrescriptionOrderID');

                if ($customerType === '1') {
                    $query->where('c.GCCustomerType', '=', 'X004^500');
                } else if ($customerType === '2') {
                    $query->whereIn('c.GCCustomerType', ['X004^999', 'X004^251', 'X004^300']);
                } else if ($customerType === '3') {
                    $query->whereIn('c.GCCustomerType', ['X004^100', 'X004^200']);
                } else if ($customerType === '4') {
                    $query->whereIn('c.GCCustomerType', ['X004^250', 'X004^400', 'X004^201']);
                }

                if ($statusOrder !== 'all') {
                    if ($statusOrder === '2') {
                        $query
                            // ->whereNot('sc3.StandardCodeName', '=', 'CLOSED')
                            // ->WhereNot('sc2.StandardCodeName', '=', 'PROCESSED')
                            ->whereNull('poh.ClosedDate');
                    } else {
                        $query
                            ->where(function ($query) {
                                $query
                                    ->whereNotNull('poh.ClosedDate');
                                // ->orWhere('sc3.StandardCodeName', '=', 'CLOSED')
                                // ->orWhere('sc2.StandardCodeName', '=', 'PROCESSED');
                            });
                    }
                }
                if (!empty($search)) {
                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('p.FullName', 'LIKE', "%{$search}%")
                            ->orWhere('p.MedicalNo', 'LIKE', "%{$search}%")
                            ->orWhere('sc.StandardCodeName', 'LIKE', "%{$search}%");
                    });
                }
            });

        if ($statusOrder !== 'all') {
            if ($statusOrder === '2') {
                $queryNonRacikan
                    ->whereNull('poh.ClosedDate');
                // ->whereNot('sc3.StandardCodeName', '=', 'CLOSED')
                // ->WhereNot('sc2.StandardCodeName', '=', 'PROCESSED');
            } else {
                $queryNonRacikan
                    ->where(function ($queryNonRacikan) {
                        $queryNonRacikan
                            ->whereNotNull('poh.ClosedDate');
                        // ->orWhere('sc3.StandardCodeName', '=', 'CLOSED')
                        // ->orWhere('sc2.StandardCodeName', '=', 'PROCESSED');
                    });
            }
        }

        if ($customerType === '1') {
            $queryNonRacikan->where('c.GCCustomerType', '=', 'X004^500');
        } else if ($customerType === '2') {
            $queryNonRacikan->whereIn('c.GCCustomerType', ['X004^999', 'X004^251', 'X004^300']);
        } else if ($customerType === '3') {
            $queryNonRacikan->whereIn('c.GCCustomerType', ['X004^100', 'X004^200']);
        } else if ($customerType === '4') {
            $queryNonRacikan->whereIn('c.GCCustomerType', ['X004^250', 'X004^400', 'X004^201']);
        }
        if (!empty($search)) {
            $queryNonRacikan->where(function ($query) use ($search) {
                $query
                    ->where('p.FullName', 'LIKE', "%{$search}%")
                    ->orWhere('p.MedicalNo', 'LIKE', "%{$search}%")
                    ->orWhere('sc.StandardCodeName', 'LIKE', "%{$search}%");
            });
        }

        // Gabungkan hasil kedua query berdasarkan jenisOrder
        if ($jenisOrder == '1') {
            $combinedQuery = $queryRacikan;
        } elseif ($jenisOrder == '2') {
            $combinedQuery = $queryNonRacikan;
        } else {
            $combinedQuery = $queryRacikan->unionAll($queryNonRacikan);
        }

        $startDate = Carbon::createFromFormat('Y-m-d', trim($startDate));
        $endDate = Carbon::createFromFormat('Y-m-d', trim($endDate));

        $startDate = $startDate->format('d-m-Y');
        $endDate = $endDate->format('d-m-Y');

        $query = DB::table(DB::raw("({$combinedQuery->toSql()}) as combined"))
            ->mergeBindings($combinedQuery); // Pindahkan binding dari query sebelumnya
        if ($sortBy === '1') {
            $query->orderBy('CreatedDate', 'asc');
        } else {
            $query->orderBy('CreatedDate', 'desc');
        }
        $results = $query->paginate($perPage)
            ->appends([
                'status_order' => $statusOrder,
                'location' => $location,
                'customer_type' => $customerType,
                'date' => $startDate . ' to ' . $endDate,
                'per_page' => $perPage,
                'search' => $search
            ]);

        return $results;

        // $query = DB::table('PrescriptionOrderHd as po')
        //     ->leftJoin('vRegistration as vr', function ($join) {
        //         $join->on('po.VisitID', '=', 'vr.VisitID')
        //             ->where('vr.GCRegistrationStatus', '<>', 'X020^006');
        //     })
        //     ->leftJoin('Location as loc', function ($join) {
        //         $join->on('po.LocationID', '=', 'loc.LocationID')
        //             ->where('loc.IsDeleted', '=', 0);
        //     })
        //     ->leftJoin('StandardCode as sc1', 'po.GCPrescriptionType', '=', 'sc1.StandardCodeID')
        //     ->leftJoin('StandardCode as sc2', 'po.GCOrderStatus', '=', 'sc2.StandardCodeID')
        //     ->leftJoin('StandardCode as sc3', 'po.GCTransactionStatus', '=', 'sc3.StandardCodeID')
        //     ->leftJoin('StandardCode as sc4', 'vr.GCRegistrationStatus', '=', 'sc4.StandardCodeID')
        //     ->leftJoin('User as us', 'po.ProposedBy', '=', 'us.UserID')
        //     ->select(
        //         'po.LocationID',
        //         'loc.LocationName',
        //         'vr.ServiceUnitName',
        //         'po.PrescriptionOrderNo',
        //         'po.PrescriptionDate',
        //         'po.PrescriptionTime',
        //         'vr.RegistrationNo',
        //         'vr.MedicalNo',
        //         'vr.PatientName',
        //         'vr.CustomerType',
        //         'vr.GCCustomerType',
        //         'vr.VisitID',
        //         'vr.GCRegistrationStatus',
        //         DB::raw('sc4.StandardCodeName AS StatusRegistrasi'),
        //         'po.GCPrescriptionType',
        //         DB::raw('sc1.StandardCodeName AS JenisResep'),
        //         'po.ParamedicID',
        //         'po.ClassID',
        //         'po.DispensaryServiceUnitID',
        //         'po.GCTransactionStatus',
        //         DB::raw('sc3.StandardCodeName AS StatusTransaksi'),
        //         'po.GCOrderStatus',
        //         DB::raw('sc3.StandardCodeName AS StatusOrder'),
        //         'po.SendOrderDateTime',
        //         'po.SendOrderBy',
        //         DB::raw('po.StartDate AS StartDateFarmasi'),
        //         DB::raw('po.StartTime AS StartTimeFarmasi'),
        //         DB::raw('po.CompleteDate AS CompleteDateFarmasi'),
        //         DB::raw('po.CompleteTime AS CompleteTimeFarmasi'),
        //         'po.CompleteByName',
        //         DB::raw('po.ClosedDate AS ClosedDateFarmasi'),
        //         DB::raw('po.ClosedTime AS ClosedTimeFarmasi'),
        //         'po.ClosedByName',
        //         'po.Remarks',
        //         'po.CreatedBy',
        //         'po.CreatedDate',
        //         'po.LastUpdatedBy',
        //         'po.LastUpdatedDate',
        //         'po.ProposedBy',
        //         DB::raw('us.UserName AS Petugas')
        //     )
        //     ->whereBetween('po.PrescriptionDate', [$startDate, $endDate])
        //     // ->where('po.LocationID', '=', '11')
        //     ->where('po.GCTransactionStatus', '<>', 'X121^999');

        // if (!empty($location)) {
        //     $query->where('po.LocationID', '=', $location);
        // } else {
        //     $query->whereIn('po.LocationID', $this->listLocationID);
        // }

        // if (!empty($customerType)) {
        //     $query->where('vr.GCCustomerType', '=', $customerType);
        // }

        // if (!empty($search)) {
        //     $query->where('vr.ServiceUnitName', 'like', '%' . $search . '%')
        //         ->orWhere('vr.CustomerType', 'like', '%' . $search . '%');
        // }

        // $results = $query->orderBy('po.CreatedDate', 'desc')->get();
        // return $results;
    }

    function getCountOrderPharmacyRajalV2($startDate, $endDate)
    {
        // Subquery untuk Racikan
        $racikan = DB::table('PrescriptionOrderHd as poh')
            ->select(DB::raw("
            DISTINCT poh.DispensaryServiceUnitID,
            CASE
                WHEN (sud.ServiceUnitName = 'FARMASI RAWAT INAP NON UDD')
                    THEN 'FARMASI RANAP NON UDD'
                ELSE
                    sud.ServiceUnitName
                END
            AS 'Dispensary',
            CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME) AS 'ClosedDateTime',
            CASE
                WHEN (poh.ClosedDate IS NOT NULL AND poh.SendOrderDateTime IS NOT NULL)
                    THEN DATEDIFF(SECOND, poh.SendOrderDateTime, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                WHEN (poh.ClosedDate IS NOT NULL AND pch.ProposedDate IS NOT NULL)
                    THEN DATEDIFF(SECOND, pch.ProposedDate, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                ELSE
                    NULL
            END AS 'DurationMinutes',
            su.ServiceUnitName as 'Poli',
            r.RegistrationNo,
            p.MedicalNo,
            p.FullName as 'PatientName',
            pm.FullName as 'Nama Dokter',
            ua.FullName as 'Orderer',
            sc2.StandardCodeName as 'StatusOrder',
            poh.PrescriptionOrderID,
            poh.PrescriptionOrderNo,
            pch.TransactionID,
            pch.TransactionNo,
            poh.SendOrderDateTime,
            pch.ProposedDate as ProposedDateTime,
            ua2.FullName as 'User Propose',
            poh.ClosedDate as ClosedDateFarmasi,
            poh.ClosedTime as ClosedTimeFarmasi,
            bp.BusinessPartnerID,
            c.GCCustomerType,
            sc.StandardCodeName as 'Penjamin',
            bp.BusinessPartnerName,
            pch.TotalAmount,
            poh.CreatedDate,
            sc3.StandardCodeName as 'StatusTransaksi',
            'RACIKAN' as 'JenisResep'
        "))
            ->join('PrescriptionOrderDt as pod', 'poh.PrescriptionOrderID', '=', 'pod.PrescriptionOrderID')
            ->leftJoin('PatientChargesHd as pch', function ($join) {
                $join->on('poh.PrescriptionOrderID', '=', 'pch.PrescriptionOrderID')
                    ->whereNotIn('pch.GCTransactionStatus', ['X121^999']);
            })
            ->join('ConsultVisit as cv', 'poh.VisitID', '=', 'cv.VisitID')
            ->join('Registration as r', 'cv.RegistrationID', '=', 'r.RegistrationID')
            ->join('Patient as p', 'r.MRN', '=', 'p.MRN')
            ->join('BusinessPartners as bp', 'r.BusinessPartnerID', '=', 'bp.BusinessPartnerID')
            ->join('Customer as c', 'bp.BusinessPartnerID', '=', 'c.BusinessPartnerID')
            ->join('StandardCode as sc', function ($join) {
                $join->on('c.GCCustomerType', '=', 'sc.StandardCodeID')
                    ->whereNotIn('c.GCCustomerType', ['X004^250', 'X004^201', 'X004^400']);
            })
            ->join('StandardCode as sc2', 'poh.GCTransactionStatus', '=', 'sc2.StandardCodeID')
            ->leftJoin('StandardCode as sc3', 'pch.GCTransactionStatus', '=', 'sc3.StandardCodeID')
            ->join('HealthcareServiceUnit as hsu', 'cv.HealthcareServiceUnitID', '=', 'hsu.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as su', 'hsu.ServiceUnitID', '=', 'su.ServiceUnitID')
            ->join('HealthcareServiceUnit as hsud', 'poh.DispensaryServiceUnitID', '=', 'hsud.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as sud', 'hsud.ServiceUnitID', '=', 'sud.ServiceUnitID')
            ->join('ParamedicMaster as pm', 'poh.ParamedicID', '=', 'pm.ParamedicID')
            ->join('UserAttribute as ua', 'poh.CreatedBy', '=', 'ua.UserID')
            ->leftJoin('UserAttribute as ua2', 'pch.ProposedBy', '=', 'ua2.UserID')
            ->whereBetween('poh.CreatedDate', [$startDate, $endDate])
            ->whereNotIn('poh.GCTransactionStatus', ['X121^999'])
            ->whereNot('pch.TotalAmount', '=', '.00')
            ->whereNotIn('r.MRN', [10, 527556])
            ->whereNotIn('poh.DispensaryServiceUnitID', [101, 133])
            ->where('pod.IsCompound', 1);

        // Subquery untuk Non-Racikan
        $nonRacikan = DB::table('PrescriptionOrderHd as poh')
            ->select(DB::raw("
            DISTINCT poh.DispensaryServiceUnitID,
            CASE
                WHEN (sud.ServiceUnitName = 'FARMASI RAWAT INAP NON UDD')
                    THEN 'FARMASI RANAP NON UDD'
                ELSE
                    sud.ServiceUnitName
            END AS 'Dispensary',
            CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME) AS 'ClosedDateTime',
            CASE
                WHEN (poh.ClosedDate IS NOT NULL AND poh.SendOrderDateTime IS NOT NULL)
                    THEN DATEDIFF(SECOND, poh.SendOrderDateTime, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                WHEN (poh.ClosedDate IS NOT NULL AND pch.ProposedDate IS NOT NULL)
                    THEN DATEDIFF(SECOND, pch.ProposedDate, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                ELSE
                    NULL
            END AS 'DurationMinutes',
            su.ServiceUnitName as 'Poli',
            r.RegistrationNo,
            p.MedicalNo,
            p.FullName as 'PatientName',
            pm.FullName as 'Nama Dokter',
            ua.FullName as 'User Buat',
            sc2.StandardCodeName as 'StatusOrder',
            poh.PrescriptionOrderID,
            poh.PrescriptionOrderNo,
            pch.TransactionID,
            pch.TransactionNo,
            poh.SendOrderDateTime,
            pch.ProposedDate as ProposedDateTime,
            ua2.FullName as 'User Propose',
            poh.ClosedDate as ClosedDateFarmasi,
            poh.ClosedTime as ClosedTimeFarmasi,
            bp.BusinessPartnerID,
            c.GCCustomerType,
            sc.StandardCodeName as 'Penjamin',
            bp.BusinessPartnerName,
            pch.TotalAmount,
            poh.CreatedDate,
            sc3.StandardCodeName as 'StatusTransaksi',
            'NON RACIKAN' as 'JenisResep'
        "))
            ->leftJoin('PatientChargesHd as pch', function ($join) {
                $join->on('poh.PrescriptionOrderID', '=', 'pch.PrescriptionOrderID')
                    ->whereNotIn('pch.GCTransactionStatus', ['X121^999']);
            })
            ->join('ConsultVisit as cv', 'poh.VisitID', '=', 'cv.VisitID')
            ->join('Registration as r', 'cv.RegistrationID', '=', 'r.RegistrationID')
            ->join('Patient as p', 'r.MRN', '=', 'p.MRN')
            ->join('BusinessPartners as bp', 'r.BusinessPartnerID', '=', 'bp.BusinessPartnerID')
            ->join('Customer as c', 'bp.BusinessPartnerID', '=', 'c.BusinessPartnerID')
            ->join('StandardCode as sc', function ($join) {
                $join->on('c.GCCustomerType', '=', 'sc.StandardCodeID')
                    ->whereNotIn('c.GCCustomerType', ['X004^250', 'X004^201', 'X004^400']);
            })
            ->join('StandardCode as sc2', 'poh.GCTransactionStatus', '=', 'sc2.StandardCodeID')
            ->leftJoin('StandardCode as sc3', 'pch.GCTransactionStatus', '=', 'sc3.StandardCodeID')
            ->join('HealthcareServiceUnit as hsu', 'cv.HealthcareServiceUnitID', '=', 'hsu.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as su', 'hsu.ServiceUnitID', '=', 'su.ServiceUnitID')
            ->join('HealthcareServiceUnit as hsud', 'poh.DispensaryServiceUnitID', '=', 'hsud.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as sud', 'hsud.ServiceUnitID', '=', 'sud.ServiceUnitID')
            ->join('ParamedicMaster as pm', 'poh.ParamedicID', '=', 'pm.ParamedicID')
            ->join('UserAttribute as ua', 'poh.CreatedBy', '=', 'ua.UserID')
            ->leftJoin('UserAttribute as ua2', 'pch.ProposedBy', '=', 'ua2.UserID')
            ->whereBetween('poh.CreatedDate', [$startDate, $endDate])
            ->whereNotIn('poh.GCTransactionStatus', ['X121^999'])
            ->whereNot('pch.TotalAmount', '=', '.00')
            ->whereNotIn('poh.DispensaryServiceUnitID', [101, 133])
            ->whereNotIn('r.MRN', [10, 527556])
            ->whereNotExists(function ($query) use ($startDate, $endDate) {
                $query->select(DB::raw(1))
                    ->from('PrescriptionOrderHd as poh_inner')
                    ->join('PrescriptionOrderDt as pod_inner', 'poh_inner.PrescriptionOrderID', '=', 'pod_inner.PrescriptionOrderID')
                    ->leftJoin('PatientChargesHd as pch_inner', function ($join) {
                        $join->on('poh_inner.PrescriptionOrderID', '=', 'pch_inner.PrescriptionOrderID')
                            ->whereNotIn('pch_inner.GCTransactionStatus', ['X121^999']);
                    })
                    ->join('ConsultVisit as cv_inner', 'poh_inner.VisitID', '=', 'cv_inner.VisitID')
                    ->join('Registration as r_inner', 'cv_inner.RegistrationID', '=', 'r_inner.RegistrationID')
                    ->join('BusinessPartners as bp_inner', 'r_inner.BusinessPartnerID', '=', 'bp_inner.BusinessPartnerID')
                    ->join('Customer as c_inner', 'bp_inner.BusinessPartnerID', '=', 'c_inner.BusinessPartnerID')
                    ->whereBetween('poh.CreatedDate', [$startDate, $endDate])
                    ->whereNotIn('poh_inner.GCTransactionStatus', ['X121^999'])
                    ->whereNot('pch.TotalAmount', '=', '.00')
                    ->whereNotIn('r.MRN', [10, 527556])
                    ->where('pod_inner.IsCompound', 1)
                    ->whereNotIn('poh.DispensaryServiceUnitID', [101, 133])
                    ->whereColumn('poh.PrescriptionOrderID', 'poh_inner.PrescriptionOrderID');
            });

        // dd($nonRacikan);

        // Gabungkan kedua subquery dengan UNION ALL
        $combinedOrders = $racikan->unionAll($nonRacikan);

        // Lakukan agregasi pada hasil gabungan dari subquery
        $results = DB::table(DB::raw("({$combinedOrders->toSql()}) as combined"))
            ->mergeBindings($combinedOrders)
            ->selectRaw("
                combined.Dispensary AS LocationName,
                combined.DispensaryServiceUnitID,
                COUNT(*) AS TotalOrder,
                SUM(
                    CASE
                        WHEN combined.DurationMinutes IS NOT NULL
                            THEN 1
                        ELSE 0
                    END
                ) AS TotalOrderSelesai,
                SUM(
                    CASE
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationMinutes <= 3600 AND combined.JenisResep = 'RACIKAN')
                            THEN 1
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationMinutes <= 1800 AND combined.JenisResep = 'NON RACIKAN')
                            THEN 1
                        ELSE 0
                    END
                ) AS TotalOrderOnTime,
                SUM(
                    CASE
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationMinutes > 3600 AND combined.JenisResep = 'RACIKAN')
                            THEN 1
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationMinutes > 1800 AND combined.JenisResep = 'NON RACIKAN')
                            THEN 1
                        ELSE 0
                    END
                ) AS TotalOrderLateTime,
                AVG(
                    CASE
                        WHEN (combined.JenisResep = 'RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                            THEN combined.DurationMinutes
                        END
                ) AS AverageDurationRacikan,
                AVG(
                    CASE
                        WHEN (combined.JenisResep = 'NON RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                    THEN
                        combined.DurationMinutes
                    END
                ) AS AverageDurationNonRacikan,
                SUM(
                    CASE
                        WHEN (combined.JenisResep = 'RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                            THEN combined.DurationMinutes
                    END
                ) AS SumDurationRacikan,
                SUM(
                    CASE
                        WHEN (combined.JenisResep = 'NON RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                    THEN
                        combined.DurationMinutes
                    END
                ) AS SumDurationNonRacikan,
                COUNT(
                    CASE
                        WHEN (combined.JenisResep = 'RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                            THEN combined.DurationMinutes
                        END
                ) AS CountDurationRacikan,
                COUNT(
                    CASE
                        WHEN (combined.JenisResep = 'NON RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                            THEN
                                combined.DurationMinutes
                    END
                ) AS CountDurationNonRacikan,
                COUNT(*) AS TotalOrder,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NULL THEN 1 ELSE 0 END) AS TotalOrderUnClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NULL AND combined.JenisResep = 'NON RACIKAN' THEN 1 ELSE 0 END) AS TotalOrderNonRacikanUnClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NULL AND combined.JenisResep = 'RACIKAN' THEN 1 ELSE 0 END) AS TotalOrderRacikanUnClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NOT NULL THEN 1 ELSE 0 END) AS TotalOrderClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NOT NULL AND combined.JenisResep = 'NON RACIKAN' THEN 1 ELSE 0 END) AS TotalOrderNonRacikanClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NOT NULL AND combined.JenisResep = 'RACIKAN' THEN 1 ELSE 0 END) AS TotalOrderRacikanClosed
            ")
            ->groupBy('combined.DispensaryServiceUnitID')
            ->groupBy('combined.Dispensary')
            ->orderBy('combined.Dispensary')
            ->get();
        // AVG(DurationMinutes) AS AverageDuration,
        // DATEDIFF(
        //     SECOND,
        //     poh.SendOrderDateTime,
        //     CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME)
        // ) AS 'DurationMinutes',
        return $results;

        // //query versi 1
        // $query = DB::table('PrescriptionOrderHd as po')
        //     ->selectRaw('
        //         loc.LocationName,
        //         COUNT(po.[LocationID]) AS TotalOrder,
        //         SUM(CASE WHEN po.ClosedDate IS NULL THEN 1 ELSE 0 END) AS TotalOrderUnClosed,
        //         SUM(CASE WHEN po.ClosedDate IS NOT NULL THEN 1 ELSE 0 END) AS TotalOrderClosed
        //     ')
        //     ->leftJoin('vRegistration as vr', function ($join) {
        //         $join->on('po.VisitID', '=', 'vr.VisitID')
        //             ->where('vr.GCRegistrationStatus', '<>', 'X020^006');
        //     })
        //     ->leftJoin('Location as loc', function ($join) {
        //         $join->on('po.LocationID', '=', 'loc.LocationID')
        //             ->where('loc.IsDeleted', '=', 0);
        //     })
        //     ->leftJoin('StandardCode as sc1', 'po.GCPrescriptionType', '=', 'sc1.StandardCodeID')
        //     ->leftJoin('StandardCode as sc2', 'po.GCOrderStatus', '=', 'sc2.StandardCodeID')
        //     ->leftJoin('StandardCode as sc3', 'po.GCTransactionStatus', '=', 'sc3.StandardCodeID')
        //     ->leftJoin('StandardCode as sc4', 'vr.GCRegistrationStatus', '=', 'sc4.StandardCodeID')
        //     ->leftJoin('User as us', 'po.ProposedBy', '=', 'us.UserID')
        //     ->whereBetween('po.PrescriptionDate', [$startDate, $endDate])
        //     ->where('po.GCTransactionStatus', '<>', 'X121^999');

        // if (!empty($location)) {
        //     $query->where('po.LocationID', '=', $location);
        // } else {
        //     $query->whereIn('po.LocationID', $this->listLocationID);
        // }

        // if (!empty($customerType)) {
        //     $query->where('vr.GCCustomerType', '=', $customerType);
        // } else {
        //     $query->whereIn('vr.GCCustomerType', $this->listCustomerTypeID);
        // }
        // $results = $query->groupBy('loc.LocationName')
        //     ->get();

        // //query versi 2
        // $query = "
        //     SELECT
        //         loc.LocationName,
        //         COUNT(po.[LocationID]) AS TotalOrder
        //     FROM [MS_RSDOSOBA].[dbo].[PrescriptionOrderHd] po
        //     LEFT JOIN [vRegistration] vr ON po.VisitID = vr.VisitID AND vr.GCRegistrationStatus <> 'X020^006'
        //     LEFT JOIN [Location] loc ON po.LocationID = loc.LocationID AND loc.IsDeleted = 0
        //     LEFT JOIN [StandardCode] sc1 ON po.[GCPrescriptionType] = sc1.StandardCodeID
        //     LEFT JOIN [StandardCode] sc2 ON po.[GCOrderStatus] = sc2.StandardCodeID
        //     LEFT JOIN [StandardCode] sc3 ON po.[GCTransactionStatus] = sc3.StandardCodeID
        //     LEFT JOIN [StandardCode] sc4 ON vr.[GCRegistrationStatus] = sc4.StandardCodeID
        //     LEFT JOIN [User] us ON po.[ProposedBy] = us.UserID
        //     WHERE po.[PrescriptionDate] BETWEEN ? AND ?
        //         AND po.[GCTransactionStatus] <> 'X121^999'
        //         AND po.[LocationID] IN ('11', '12', '163')
        //         AND vr.CustomerType IN ('BPJS - Kemenkes', 'Pribadi', 'Rekanan', 'Karyawan - FASKES')
        //     GROUP BY loc.LocationName
        // ";
        // $results = DB::select($query, [$startDate, $endDate]);
        return $results;
    }

    function getCountOrderPharmacyRajalByCustomerTypeV2($startDate, $endDate, $location)
    {
        // Subquery untuk Racikan
        $racikan = DB::table('PrescriptionOrderHd as poh')
            ->distinct()
            ->select(DB::raw("
                poh.DispensaryServiceUnitID,
                sud.ServiceUnitName as 'Dispensary',
                CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME) AS 'ClosedDateTime',
                CASE
                    WHEN (poh.ClosedDate IS NOT NULL AND poh.SendOrderDateTime IS NOT NULL)
                        THEN DATEDIFF(SECOND, poh.SendOrderDateTime, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                    WHEN (poh.ClosedDate IS NOT NULL AND pch.ProposedDate IS NOT NULL)
                        THEN DATEDIFF(SECOND, pch.ProposedDate, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                    ELSE
                        NULL
                END AS 'DurationMinutes',
                CASE
                    WHEN c.GCCustomerType = 'X004^500'
                        THEN 'BPJS - Kemenkes'
                    WHEN c.GCCustomerType IN ('X004^999', 'X004^251', 'X004^300')
                        THEN 'Personal'
                    WHEN c.GCCustomerType IN ('X004^100', 'X004^200')
                        THEN 'Asuransi'
                END AS PenjaminGroup,
                su.ServiceUnitName as 'Poli',
                r.RegistrationNo,
                p.MedicalNo,
                p.FullName as 'PatientName',
                pm.FullName as 'Nama Dokter',
                ua.FullName as 'Orderer',
                sc2.StandardCodeName as 'StatusOrder',
                poh.PrescriptionOrderID,
                poh.PrescriptionOrderNo,
                pch.TransactionID,
                pch.TransactionNo,
                poh.SendOrderDateTime,
                pch.ProposedDate as ProposedDateTime,
                ua2.FullName as 'User Propose',
                poh.ClosedDate as ClosedDateFarmasi,
                poh.ClosedTime as ClosedTimeFarmasi,
                bp.BusinessPartnerID,
                c.GCCustomerType,
                sc.StandardCodeName as 'Penjamin',
                bp.BusinessPartnerName,
                pch.TotalAmount,
                poh.CreatedDate,
                sc3.StandardCodeName as 'StatusTransaksi',
                'RACIKAN' as 'JenisResep'"))
            ->join('PrescriptionOrderDt as pod', 'poh.PrescriptionOrderID', '=', 'pod.PrescriptionOrderID')
            ->leftJoin('PatientChargesHd as pch', function ($join) {
                $join->on('poh.PrescriptionOrderID', '=', 'pch.PrescriptionOrderID')
                    ->whereNotIn('pch.GCTransactionStatus', ['X121^999']);
            })
            ->join('ConsultVisit as cv', 'poh.VisitID', '=', 'cv.VisitID')
            ->join('Registration as r', 'cv.RegistrationID', '=', 'r.RegistrationID')
            ->join('Patient as p', 'r.MRN', '=', 'p.MRN')
            ->join('BusinessPartners as bp', 'r.BusinessPartnerID', '=', 'bp.BusinessPartnerID')
            ->join('Customer as c', 'bp.BusinessPartnerID', '=', 'c.BusinessPartnerID')
            ->join('StandardCode as sc', function ($join) {
                $join->on('c.GCCustomerType', '=', 'sc.StandardCodeID')
                    ->whereNotIn('c.GCCustomerType', ['X004^250', 'X004^201', 'X004^400']);
            })
            ->join('StandardCode as sc2', 'poh.GCTransactionStatus', '=', 'sc2.StandardCodeID')
            ->leftJoin('StandardCode as sc3', 'pch.GCTransactionStatus', '=', 'sc3.StandardCodeID')
            ->join('HealthcareServiceUnit as hsu', 'cv.HealthcareServiceUnitID', '=', 'hsu.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as su', 'hsu.ServiceUnitID', '=', 'su.ServiceUnitID')
            ->join('HealthcareServiceUnit as hsud', 'poh.DispensaryServiceUnitID', '=', 'hsud.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as sud', 'hsud.ServiceUnitID', '=', 'sud.ServiceUnitID')
            ->join('ParamedicMaster as pm', 'poh.ParamedicID', '=', 'pm.ParamedicID')
            ->join('UserAttribute as ua', 'poh.CreatedBy', '=', 'ua.UserID')
            ->leftJoin('UserAttribute as ua2', 'pch.ProposedBy', '=', 'ua2.UserID')
            ->whereBetween('poh.CreatedDate', [$startDate, $endDate])
            ->where('poh.DispensaryServiceUnitID', $location)
            ->whereNot('pch.TotalAmount', '=', '.00')
            ->whereNotIn('r.MRN', [10, 527556])
            ->whereNotIn('poh.GCTransactionStatus', ['X121^999'])
            ->where('pod.IsCompound', 1);

        // Subquery untuk Non-Racikan
        $nonRacikan = DB::table('PrescriptionOrderHd as poh')
            ->distinct()
            ->select(DB::raw("
                poh.DispensaryServiceUnitID,
                sud.ServiceUnitName as 'Dispensary',
                CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME) AS 'ClosedDateTime',
                CASE
                    WHEN (poh.ClosedDate IS NOT NULL AND poh.SendOrderDateTime IS NOT NULL)
                        THEN DATEDIFF(SECOND, poh.SendOrderDateTime, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                    WHEN (poh.ClosedDate IS NOT NULL AND pch.ProposedDate IS NOT NULL)
                        THEN DATEDIFF(SECOND, pch.ProposedDate, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                    ELSE
                        NULL
                END AS 'DurationMinutes',
                CASE
                    WHEN c.GCCustomerType = 'X004^500'
                        THEN 'BPJS - Kemenkes'
                    WHEN c.GCCustomerType IN ('X004^999', 'X004^251', 'X004^300')
                        THEN 'Personal'
                    WHEN c.GCCustomerType IN ('X004^100', 'X004^200')
                        THEN 'Asuransi'
                END AS PenjaminGroup,
                su.ServiceUnitName as 'Poli',
                r.RegistrationNo,
                p.MedicalNo,
                p.FullName as 'PatientName',
                pm.FullName as 'Nama Dokter',
                ua.FullName as 'User Buat',
                sc2.StandardCodeName as 'StatusOrder',
                poh.PrescriptionOrderID,
                poh.PrescriptionOrderNo,
                pch.TransactionID,
                pch.TransactionNo,
                poh.SendOrderDateTime,
                pch.ProposedDate as ProposedDateTime,
                ua2.FullName as 'User Propose',
                poh.ClosedDate as ClosedDateFarmasi,
                poh.ClosedTime as ClosedTimeFarmasi,
                bp.BusinessPartnerID,
                c.GCCustomerType,
                sc.StandardCodeName as 'Penjamin',
                bp.BusinessPartnerName,
                pch.TotalAmount,
                poh.CreatedDate,
                sc3.StandardCodeName as 'StatusTransaksi',
                'NON RACIKAN' as 'JenisResep'
            "))
            ->leftJoin('PatientChargesHd as pch', function ($join) {
                $join->on('poh.PrescriptionOrderID', '=', 'pch.PrescriptionOrderID')
                    ->whereNotIn('pch.GCTransactionStatus', ['X121^999']);
            })
            ->join('ConsultVisit as cv', 'poh.VisitID', '=', 'cv.VisitID')
            ->join('Registration as r', 'cv.RegistrationID', '=', 'r.RegistrationID')
            ->join('Patient as p', 'r.MRN', '=', 'p.MRN')
            ->join('BusinessPartners as bp', 'r.BusinessPartnerID', '=', 'bp.BusinessPartnerID')
            ->join('Customer as c', 'bp.BusinessPartnerID', '=', 'c.BusinessPartnerID')
            ->join('StandardCode as sc', function ($join) {
                $join->on('c.GCCustomerType', '=', 'sc.StandardCodeID')
                    ->whereNotIn('c.GCCustomerType', ['X004^250', 'X004^201', 'X004^400']);
            })
            ->join('StandardCode as sc2', 'poh.GCTransactionStatus', '=', 'sc2.StandardCodeID')
            ->leftJoin('StandardCode as sc3', 'pch.GCTransactionStatus', '=', 'sc3.StandardCodeID')
            ->join('HealthcareServiceUnit as hsu', 'cv.HealthcareServiceUnitID', '=', 'hsu.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as su', 'hsu.ServiceUnitID', '=', 'su.ServiceUnitID')
            ->join('HealthcareServiceUnit as hsud', 'poh.DispensaryServiceUnitID', '=', 'hsud.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as sud', 'hsud.ServiceUnitID', '=', 'sud.ServiceUnitID')
            ->join('ParamedicMaster as pm', 'poh.ParamedicID', '=', 'pm.ParamedicID')
            ->join('UserAttribute as ua', 'poh.CreatedBy', '=', 'ua.UserID')
            ->leftJoin('UserAttribute as ua2', 'pch.ProposedBy', '=', 'ua2.UserID')
            ->whereBetween('poh.CreatedDate', [$startDate, $endDate])
            ->where('poh.DispensaryServiceUnitID', $location)
            ->whereNotIn('poh.GCTransactionStatus', ['X121^999'])
            ->whereNot('pch.TotalAmount', '=', '.00')
            ->whereNotIn('r.MRN', [10, 527556])
            ->whereNotExists(function ($query) use ($startDate, $endDate, $location) {
                $query->select(DB::raw(1))
                    ->from('PrescriptionOrderHd as poh_inner')
                    ->join('PrescriptionOrderDt as pod_inner', 'poh_inner.PrescriptionOrderID', '=', 'pod_inner.PrescriptionOrderID')
                    ->leftJoin('PatientChargesHd as pch_inner', function ($join) {
                        $join->on('poh_inner.PrescriptionOrderID', '=', 'pch_inner.PrescriptionOrderID')
                            ->whereNotIn('pch_inner.GCTransactionStatus', ['X121^999']);
                    })
                    ->join('ConsultVisit as cv_inner', 'poh_inner.VisitID', '=', 'cv_inner.VisitID')
                    ->join('Registration as r_inner', 'cv_inner.RegistrationID', '=', 'r_inner.RegistrationID')
                    ->join('BusinessPartners as bp_inner', 'r_inner.BusinessPartnerID', '=', 'bp_inner.BusinessPartnerID')
                    ->join('Customer as c_inner', 'bp_inner.BusinessPartnerID', '=', 'c_inner.BusinessPartnerID')
                    ->whereBetween('poh.CreatedDate', [$startDate, $endDate])
                    ->where('poh_inner.DispensaryServiceUnitID', $location)
                    ->whereNotIn('poh_inner.GCTransactionStatus', ['X121^999'])
                    ->whereNot('pch.TotalAmount', '=', '.00')
                    ->whereNotIn('r.MRN', [10, 527556])
                    ->where('pod_inner.IsCompound', 1)
                    ->whereColumn('poh.PrescriptionOrderID', 'poh_inner.PrescriptionOrderID');
            });

        // Gabungkan kedua subquery dengan UNION ALL
        $combinedOrders = $racikan->unionAll($nonRacikan);

        // Lakukan agregasi pada hasil gabungan dari subquery
        $results = DB::table(DB::raw("({$combinedOrders->toSql()}) as combined"))
            ->mergeBindings($combinedOrders)
            ->selectRaw("
                combined.PenjaminGroup AS CustomerType,
                COUNT(*) AS TotalOrder,
                SUM(
                    CASE
                        WHEN combined.DurationMinutes IS NOT NULL
                            THEN 1
                        ELSE 0
                    END
                ) AS TotalOrderSelesai,
                SUM(
                    CASE
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationMinutes <= 3600 AND combined.JenisResep = 'RACIKAN')
                            THEN 1
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationMinutes <= 1800 AND combined.JenisResep = 'NON RACIKAN')
                            THEN 1
                        ELSE 0
                    END
                ) AS TotalOrderOnTime,
                SUM(
                    CASE
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationMinutes > 3600 AND combined.JenisResep = 'RACIKAN')
                            THEN 1
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationMinutes > 1800 AND combined.JenisResep = 'NON RACIKAN')
                            THEN 1
                        ELSE 0
                    END
                ) AS TotalOrderLateTime,
                AVG(
                    CASE
                        WHEN (combined.JenisResep = 'RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                            THEN combined.DurationMinutes
                        END
                ) AS AverageDurationRacikan,
                AVG(
                    CASE
                        WHEN (combined.JenisResep = 'NON RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                    THEN
                        combined.DurationMinutes
                    END
                ) AS AverageDurationNonRacikan,
                SUM(
                    CASE
                        WHEN (combined.JenisResep = 'RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                            THEN combined.DurationMinutes
                    END
                ) AS SumDurationRacikan,
                SUM(
                    CASE
                        WHEN (combined.JenisResep = 'NON RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                    THEN
                        combined.DurationMinutes
                    END
                ) AS SumDurationNonRacikan,
                COUNT(
                    CASE
                        WHEN (combined.JenisResep = 'RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                            THEN combined.DurationMinutes
                        END
                ) AS CountDurationRacikan,
                COUNT(
                    CASE
                        WHEN (combined.JenisResep = 'NON RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                            THEN
                                combined.DurationMinutes
                    END
                ) AS CountDurationNonRacikan,
                COUNT(*) AS TotalOrder,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NULL THEN 1 ELSE 0 END) AS TotalOrderUnClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NULL AND combined.JenisResep = 'NON RACIKAN' THEN 1 ELSE 0 END) AS TotalOrderNonRacikanUnClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NULL AND combined.JenisResep = 'RACIKAN' THEN 1 ELSE 0 END) AS TotalOrderRacikanUnClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NOT NULL THEN 1 ELSE 0 END) AS TotalOrderClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NOT NULL AND combined.JenisResep = 'NON RACIKAN' THEN 1 ELSE 0 END) AS TotalOrderNonRacikanClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NOT NULL AND combined.JenisResep = 'RACIKAN' THEN 1 ELSE 0 END) AS TotalOrderRacikanClosed            ")
            ->groupBy('combined.PenjaminGroup')
            ->orderBy('combined.PenjaminGroup')
            ->get();
        return $results;
    }
}
