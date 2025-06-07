// app/Core/Helper/Helper.php - Funzioni helper globali
<?php
class Helper {
    
    /**
     * Formatta una data in formato italiano
     */
    public static function formatDate($date, $format = 'd/m/Y') {
        if (!$date) return '';
        
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        
        return $date->format($format);
    }
    
    /**
     * Formatta una data e ora in formato italiano
     */
    public static function formatDateTime($datetime, $format = 'd/m/Y H:i') {
        return self::formatDate($datetime, $format);
    }
    
    /**
     * Formatta un numero come valuta
     */
    public static function formatCurrency($amount, $currency = 'EUR') {
        if (!is_numeric($amount)) return '0,00 €';
        
        return number_format($amount, 2, ',', '.') . ' €';
    }
    
    /**
     * Formatta un numero
     */
    public static function formatNumber($number, $decimals = 2) {
        if (!is_numeric($number)) return '0';
        
        return number_format($number, $decimals, ',', '.');
    }
    
    /**
     * Genera un codice univoco
     */
    public static function generateCode($prefix = '', $length = 8) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $prefix . $code;
    }
    
    /**
     * Valida partita IVA italiana
     */
    public static function validatePartitaIva($partitaIva) {
        $partitaIva = preg_replace('/[^0-9]/', '', $partitaIva);
        
        if (strlen($partitaIva) !== 11) {
            return false;
        }
        
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $digit = (int)$partitaIva[$i];
            if ($i % 2 === 1) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit = $digit - 9;
                }
            }
            $sum += $digit;
        }
        
        $checkDigit = (10 - ($sum % 10)) % 10;
        return $checkDigit === (int)$partitaIva[10];
    }
    
    /**
     * Valida codice fiscale italiano
     */
    public static function validateCodiceFiscale($codiceFiscale) {
        $codiceFiscale = strtoupper(trim($codiceFiscale));
        
        if (strlen($codiceFiscale) !== 16) {
            return false;
        }
        
        if (!preg_match('/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/', $codiceFiscale)) {
            return false;
        }
        
        // Calcolo check digit
        $odd = "BAFHJNPRTVCESULDGIMOQKWZYX";
        $even = "BAKPLCQDREVOSFTGUHMINJWZYX";
        $checkChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        
        $sum = 0;
        for ($i = 0; $i < 15; $i++) {
            $char = $codiceFiscale[$i];
            if ($i % 2 === 0) {
                $sum += strpos($odd, $char);
            } else {
                $sum += strpos($even, $char);
            }
        }
        
        $checkChar = $checkChars[$sum % 26];
        return $checkChar === $codiceFiscale[15];
    }
    
    /**
     * Slug generator
     */
    public static function slug($text) {
        $text = trim($text);
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('/[^a-zA-Z0-9\-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        $text = trim($text, '-');
        
        return strtolower($text);
    }
    
    /**
     * Tronca testo
     */
    public static function truncate($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . $suffix;
    }
    
    /**
     * Converte bytes in formato leggibile
     */
    public static function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Genera password sicura
     */
    public static function generatePassword($length = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        return substr(str_shuffle($chars), 0, $length);
    }
    
    /**
     * Array di stati per select
     */
    public static function getStatiGenerici() {
        return [
            'attivo' => 'Attivo',
            'inattivo' => 'Inattivo',
            'sospeso' => 'Sospeso',
            'eliminato' => 'Eliminato'
        ];
    }
    
    /**
     * Array di stati offerte
     */
    public static function getStatiOfferte() {
        return [
            'bozza' => 'Bozza',
            'inviata' => 'Inviata',
            'accettata' => 'Accettata',
            'rifiutata' => 'Rifiutata',
            'scaduta' => 'Scaduta'
        ];
    }
    
    /**
     * Array di stati ordini
     */
    public static function getStatiOrdini() {
        return [
            'confermato' => 'Confermato',
            'in_lavorazione' => 'In Lavorazione',
            'pronto' => 'Pronto',
            'spedito' => 'Spedito',
            'consegnato' => 'Consegnato',
            'annullato' => 'Annullato'
        ];
    }
    
    /**
     * Array di stati progetti
     */
    public static function getStatiProgetti() {
        return [
            'pianificato' => 'Pianificato',
            'in_corso' => 'In Corso',
            'sospeso' => 'Sospeso',
            'completato' => 'Completato',
            'annullato' => 'Annullato'
        ];
    }
}