<?php
abstract class BaseModel {
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    protected $casts = [];
    protected $timestamps = true;
    
    protected static $database;
    protected static $queryBuilder;

    public function __construct() {
        if (self::$database === null) {
            $app = Application::getInstance();
            self::$database = $app->get('database');
            self::$queryBuilder = new QueryBuilder(self::$database);
        }
    }

    // Factory method per query builder
    public static function query() {
        $instance = new static();
        return self::$queryBuilder->table($instance->table);
    }

    // Metodi CRUD base
    public static function find($id) {
        $instance = new static();
        $result = self::$queryBuilder
            ->table($instance->table)
            ->where($instance->primaryKey, $id)
            ->first();
            
        return $result ? $instance->newInstance($result) : null;
    }

    public static function all() {
        $instance = new static();
        $results = self::$queryBuilder
            ->table($instance->table)
            ->get();
            
        return array_map(function($row) use ($instance) {
            return $instance->newInstance($row);
        }, $results);
    }

    public static function where($column, $operator = null, $value = null) {
        $instance = new static();
        return self::$queryBuilder
            ->table($instance->table)
            ->where($column, $operator, $value);
    }

    public static function create($data) {
        $instance = new static();
        
        // Filtra solo i campi fillable
        $filteredData = array_intersect_key($data, array_flip($instance->fillable));
        
        // Aggiungi timestamps se abilitati
        if ($instance->timestamps) {
            $filteredData['created_at'] = date('Y-m-d H:i:s');
            $filteredData['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $id = self::$database->insert($instance->table, $filteredData);
        return self::find($id);
    }

    public function update($data) {
        // Filtra solo i campi fillable
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        // Aggiungi timestamp di aggiornamento
        if ($this->timestamps) {
            $filteredData['updated_at'] = date('Y-m-d H:i:s');
        }
        
        self::$database->update(
            $this->table,
            $filteredData,
            "{$this->primaryKey} = ?",
            [$this->attributes[$this->primaryKey]]
        );
        
        // Aggiorna gli attributi locali
        $this->attributes = array_merge($this->attributes, $filteredData);
        
        return $this;
    }

    public function delete() {
        return self::$database->delete(
            $this->table,
            "{$this->primaryKey} = ?",
            [$this->attributes[$this->primaryKey]]
        );
    }

    public static function paginate($perPage = 15, $page = 1) {
        $instance = new static();
        $offset = ($page - 1) * $perPage;
        
        // Conta il totale
        $total = self::$queryBuilder
            ->table($instance->table)
            ->count();
            
        // Prendi i risultati paginati
        $results = self::$queryBuilder
            ->table($instance->table)
            ->limit($perPage, $offset)
            ->get();
            
        $items = array_map(function($row) use ($instance) {
            return $instance->newInstance($row);
        }, $results);
        
        return [
            'data' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }

    // Relazioni
    public function hasMany($related, $foreignKey = null, $localKey = null) {
        $foreignKey = $foreignKey ?? $this->table . '_id';
        $localKey = $localKey ?? $this->primaryKey;
        
        $relatedInstance = new $related();
        
        return self::$queryBuilder
            ->table($relatedInstance->table)
            ->where($foreignKey, $this->attributes[$localKey]);
    }

    public function belongsTo($related, $foreignKey = null, $ownerKey = null) {
        $relatedInstance = new $related();
        $foreignKey = $foreignKey ?? $relatedInstance->table . '_id';
        $ownerKey = $ownerKey ?? $relatedInstance->primaryKey;
        
        return self::$queryBuilder
            ->table($relatedInstance->table)
            ->where($ownerKey, $this->attributes[$foreignKey])
            ->first();
    }

    // Gestione attributi
    protected $attributes = [];

    private function newInstance($data) {
        $instance = new static();
        $instance->attributes = $this->castAttributes($data);
        return $instance;
    }

    private function castAttributes($data) {
        foreach ($this->casts as $key => $type) {
            if (isset($data[$key])) {
                switch ($type) {
                    case 'int':
                    case 'integer':
                        $data[$key] = (int) $data[$key];
                        break;
                    case 'float':
                    case 'double':
                        $data[$key] = (float) $data[$key];
                        break;
                    case 'bool':
                    case 'boolean':
                        $data[$key] = (bool) $data[$key];
                        break;
                    case 'array':
                    case 'json':
                        $data[$key] = json_decode($data[$key], true);
                        break;
                    case 'date':
                        $data[$key] = new DateTime($data[$key]);
                        break;
                }
            }
        }
        
        return $data;
    }

    public function __get($key) {
        return $this->attributes[$key] ?? null;
    }

    public function __set($key, $value) {
        if (in_array($key, $this->fillable)) {
            $this->attributes[$key] = $value;
        }
    }

    public function __isset($key) {
        return isset($this->attributes[$key]);
    }

    public function toArray() {
        $array = $this->attributes;
        
        // Rimuovi campi hidden
        foreach ($this->hidden as $hidden) {
            unset($array[$hidden]);
        }
        
        return $array;
    }

    public function toJson() {
        return json_encode($this->toArray());
    }
}