<?php

namespace Modules\Masjid\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasjidPaymentAttachment extends Model
{
    use HasFactory;

    protected $table = 'masjid_payment_attachments';

    protected $fillable = [
        'payment_id', 'file_path', 'original_name', 'mime_type', 'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function payment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MasjidPayment::class, 'payment_id');
    }
}