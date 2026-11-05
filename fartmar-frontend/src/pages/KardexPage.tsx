import React, { useEffect, useState } from 'react';
import { api } from '../services/api';
import { KardexMovimiento } from '../types';

export const KardexPage: React.FC = () => {
  const [movimientos, setMovimientos] = useState<KardexMovimiento[]>([]);
  const [tipoFiltro, setTipoFiltro] = useState<string>('');

  const cargarKardex = async (): Promise<void> => {
    try {
      const res = await api.get<{ data: KardexMovimiento[] } | KardexMovimiento[]>('/kardex', {
        params: { tipo_movimiento: tipoFiltro || undefined }
      });
      const data = Array.isArray(res.data) ? res.data : (res.data.data || []);
      setMovimientos(data);
    } catch (err) {
      console.error(err);
    }
  };

  useEffect(() => {
    cargarKardex();
  }, [tipoFiltro]);

  return (
    <div className="p-6 max-w-7xl mx-auto bg-white rounded-2xl shadow-xl space-y-6">
      <div className="flex flex-col md:flex-row justify-between items-center border-b pb-4 gap-4">
        <div>
          <h2 className="text-2xl font-extrabold text-gray-900">Auditoría del Kardex (RN-06)</h2>
          <p className="text-sm text-gray-500">Historial inmutable de entradas, salidas y ajustes de inventario.</p>
        </div>
        <select
          value={tipoFiltro}
          onChange={(e) => setTipoFiltro(e.target.value)}
          className="border border-gray-300 p-2.5 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
        >
          <option value="">Todos los Movimientos</option>
          <option value="DISPENSACION">DISPENSACIÓN</option>
          <option value="ENTRADA_COMPRA">ENTRADA COMPRA</option>
          <option value="SALIDA_TRASLADO">SALIDA TRASLADO</option>
          <option value="ENTRADA_TRASLADO">ENTRADA TRASLADO</option>
        </select>
      </div>

      <div className="overflow-x-auto">
        <table className="w-full text-left border-collapse">
          <thead>
            <tr className="border-b bg-gray-50 text-xs font-bold text-gray-600 uppercase">
              <th className="p-3">Fecha</th>
              <th className="p-3">Bodega</th>
              <th className="p-3">Medicamento</th>
              <th className="p-3">Tipo Movimiento</th>
              <th className="p-3">Cantidad</th>
              <th className="p-3">Saldo Ant / Nuevo</th>
              <th className="p-3">Referencia / Observaciones</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100 text-sm">
            {movimientos.length > 0 ? (
              movimientos.map((m) => (
                <tr key={m.id} className="hover:bg-gray-50">
                  <td className="p-3 text-xs text-gray-500">{new Date(m.created_at).toLocaleString()}</td>
                  <td className="p-3">Bodega #{m.bodega_id}</td>
                  <td className="p-3 font-semibold">{m.producto?.nombre || `Prod #${m.producto_id}`}</td>
                  <td className="p-3">
                    <span className="font-mono text-xs font-bold bg-slate-100 text-slate-800 px-2 py-1 rounded border">
                      {m.tipo_movimiento}
                    </span>
                  </td>
                  <td className="p-3 font-bold text-blue-600">{m.cantidad}</td>
                  <td className="p-3 text-xs font-mono">{m.saldo_anterior} \ <strong className="text-gray-900">{m.saldo_nuevo}</strong></td>
                  <td className="p-3 text-xs text-gray-500">{m.referencia} {m.observaciones ? `(${m.observaciones})` : ''}</td>
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan={7} className="p-6 text-center text-gray-500">No se encontraron movimientos registrados en el Kardex.</td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
};