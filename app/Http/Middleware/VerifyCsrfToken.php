<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // The printed CSI reports. They only read, and the same addresses
        // answer a plain GET, which never needs a token. Their filters are
        // posted to a new tab (too long for an address, see openPdf.js), and
        // the token such a post carried was the one the report page had when
        // it loaded: once that was no longer the session's, printing opened
        // "419 Page Expired" instead of the report.
        'csi/print/*',
    ];
}
