<?php
// Modules/Clienti/Models/Cliente.php
class Cliente extends BaseModel {
    protected $table = 'clienti';
    protected $fillable = ['ragione_sociale', 'partita_iva', 'email'];
    
    // Relazioni
    public function contatti() {
       // return $this->hasMany(Contatto::class, 'cliente_id');
    }
    
    public function offerte() {
      //  return $this->hasMany(Offerta::class, 'cliente_id');
    }
}