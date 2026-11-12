<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manual extends Model
{
    protected $fillable = ['application_id', 'title', 'content', 'pdf_url'];

    /**
     * Temporary PDF-only content. It is intentionally not a database field.
     * These accessors also protect deployments that still assign
     * $manual->pdf_content while an old PHP worker is being recycled.
     */
    protected $pdfContentForRendering;

    public function setPdfContentAttribute($value): void
    {
        $this->pdfContentForRendering = (string) $value;
        unset($this->attributes['pdf_content']);
    }

    public function getPdfContentAttribute(): ?string
    {
        return $this->pdfContentForRendering;
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}


