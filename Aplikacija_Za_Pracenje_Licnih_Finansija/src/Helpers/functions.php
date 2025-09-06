<?php
// Helper funkcije

// Escape HTML (protiv XSS)
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Formatiranje dinara
function formatRsd(float $amount): string {
    return number_format($amount, 2, ',', '.') . ' RSD';
}

// Formatiranje datuma
function formatDate(string $date): string {
    return date('d.m.Y', strtotime($date));
}

// Generisanje paginacijskih linkova
function paginate(int $currentPage, int $totalPages, string $baseUrl): string {
    if ($totalPages <= 1) return '';
    
    $html = '<nav><ul class="pagination justify-content-center">';
    
    // Prethodna
    $prevDisabled = $currentPage <= 1 ? 'disabled' : '';
    $prevPage = max(1, $currentPage - 1);
    $html .= "<li class='page-item $prevDisabled'>";
    $html .= "<a class='page-link' href='$baseUrl?page=$prevPage'>Prethodna</a>";
    $html .= "</li>";
    
    // Brojevi stranica
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i === $currentPage ? 'active' : '';
        $html .= "<li class='page-item $active'>";
        $html .= "<a class='page-link' href='$baseUrl?page=$i'>$i</a>";
        $html .= "</li>";
    }
    
    // Sledeća
    $nextDisabled = $currentPage >= $totalPages ? 'disabled' : '';
    $nextPage = min($totalPages, $currentPage + 1);
    $html .= "<li class='page-item $nextDisabled'>";
    $html .= "<a class='page-link' href='$baseUrl?page=$nextPage'>Sledeća</a>";
    $html .= "</li>";
    
    $html .= '</ul></nav>';
    
    return $html;
}

// Redirekcija
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

// Proveri autentifikaciju
function requireAuth(): void {
    if (!isset($_SESSION['user_id'])) {
        redirect('/auth/login.php');
    }
}

// Badge za tip transakcije
function typeBadge(string $type): string {
    $class = $type === 'prihod' ? 'success' : 'danger';
    $label = ucfirst($type);
    return "<span class='badge bg-$class'>$label</span>";
}