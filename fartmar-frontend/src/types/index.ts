export interface User {
  id: number;
  name: string;
  email: string;
  role: 'auxiliar_farmacia' | 'regente_farmacia' | 'medico' | 'auditor' | 'admin';
}

export interface Bodega {
  id: number;
  nombre: string;
  tipo_bodega: string;
}

export interface Producto {
  id: number;
  nombre: string;
  codigo_cum: string;
  es_control_especial: boolean;
  stock?: number;
}

export interface Paciente {
  id: number;
  nombre: string;
  apellido: string;
  numero_documento: string;
}

export interface Prescripcion {
  id: number;
  paciente: Paciente;
  producto: Producto;
  cantidad_prescrita: number;
  cantidad_dispensada: number;
  diagnostico: string;
  estado: string;
}

export interface LoteInventario {
  id: number;
  bodega_id: number;
  producto_id: number;
  lote: string;
  fecha_vencimiento: string;
  cantidad: number;
  stock_minimo: number;
  producto?: Producto;
  bodega?: Bodega;
}

export interface Traslado {
  id: number;
  bodega_origen_id: number;
  bodega_destino_id: number;
  estado: 'BORRADOR' | 'SOLICITADO' | 'APROBADO' | 'EN_TRANSITO' | 'RECIBIDO' | 'RECIBIDO_PARCIAL' | 'ANULADO';
  solicitante_id: number;
  aprobador_id?: number;
  observaciones?: string;
  detalles?: {
    id: number;
    lote_inventario_id: number;
    cantidad_solicitada: number;
    cantidad_despachada?: number;
    cantidad_recibida?: number;
  }[];
}

export interface KardexMovimiento {
  id: number;
  bodega_id: number;
  producto_id: number;
  tipo_movimiento: string;
  cantidad: number;
  saldo_anterior: number;
  saldo_nuevo: number;
  referencia: string;
  observaciones?: string;
  created_at: string;
  producto?: Producto;
  bodega?: Bodega;
}