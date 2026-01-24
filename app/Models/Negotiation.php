<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Helpers\CurrencyConverter;

class Negotiation extends Model
{
    use HasFactory;

    protected $table = 'negotiations';
    protected $primaryKey = 'negotiation_id';
    public $timestamps = true;

    protected $fillable = [
        'procurement_id',
        'vendor_id',
        'hps',
        'currency_hps',
        'budget',
        'currency_budget',
        'harga_final',
        'currency_harga_final',
        'tanggal_kirim',
        'tanggal_terima',
        'lead_time',
        'link',
        'notes',
    ];

    protected $casts = [
        'hps' => 'decimal:2',
        'budget' => 'decimal:2',
        'harga_final' => 'decimal:2',
        'tanggal_kirim' => 'date',
        'tanggal_terima' => 'date',
    ];

    protected $appends = [
        'deviasi_hps',
        'deviasi_budget',
    ];

    public function getHargaFinalInHpsCurrency(): ?float
    {
        if (!$this->harga_final || !$this->currency_harga_final || !$this->currency_hps) {
            return null;
        }

        return CurrencyConverter::convert(
            $this->harga_final,
            $this->currency_harga_final,
            $this->currency_hps
        );
    }

    public function getBudgetInHpsCurrency(): ?float
    {
        if (!$this->budget || !$this->currency_budget || !$this->currency_hps) {
            return null;
        }

        return CurrencyConverter::convert(
            $this->budget,
            $this->currency_budget,
            $this->currency_hps
        );
    }

    public function getDeviasiHpsAttribute(): ?float
    {
        if (!$this->hps || !$this->harga_final) {
            return null;
        }

        $hargaFinalInHps = $this->getHargaFinalInHpsCurrency();

        if ($hargaFinalInHps === null) {
            return null;
        }

        return $this->hps - $hargaFinalInHps;
    }

    public function getDeviasiBudgetAttribute(): ?float
    {
        if (!$this->budget || !$this->harga_final) {
            return null;
        }

        $budgetInHps = $this->getBudgetInHpsCurrency();
        $hargaFinalInHps = $this->getHargaFinalInHpsCurrency();

        if ($budgetInHps === null || $hargaFinalInHps === null) {
            return null;
        }

        return $budgetInHps - $hargaFinalInHps;
    }

    public function procurement()
    {
        return $this->belongsTo(
            Procurement::class,
            'procurement_id',
            'procurement_id'
        );
    }

    public function vendor()
    {
        return $this->belongsTo(
            Vendor::class,
            'vendor_id',
            'id_vendor'
        );
    }
}
