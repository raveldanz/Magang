<?php

namespace App\Enums\Concerns;

/**
 * Standar tampilan status untuk semua enum status: nama = kode sistem huruf kapital
 * (identik dengan nilai database, "_" jadi spasi), keterangan Indonesia lewat description().
 */
trait DisplaysStatusCode
{
    public function label(): string
    {
        return str_replace('_', ' ', strtoupper($this->value));
    }

    /**
     * Terima enum atau string mentah (data lama / raw query); null bila bukan status resmi.
     */
    public static function resolve(\BackedEnum|string|null $status): ?static
    {
        if ($status instanceof static) {
            return $status;
        }

        if ($status === null || $status instanceof \BackedEnum) {
            return null;
        }

        return static::tryFrom(strtolower(trim($status)));
    }

    /**
     * Tolak nilai di luar enum (dipakai penjaga simpan untuk kolom status berjenis teks biasa).
     */
    public static function assertValid(mixed $status, string $column = 'status'): void
    {
        if ($status === null || $status instanceof static || static::resolve($status) !== null) {
            return;
        }

        $value = $status instanceof \BackedEnum ? $status->value : (string) $status;

        throw new \InvalidArgumentException(sprintf(
            'Nilai %s "%s" tidak dikenal. Nilai yang sah: %s.', $column, $value, implode(', ', static::values())
        ));
    }

    public static function values(): array
    {
        return array_column(static::cases(), 'value');
    }
}
