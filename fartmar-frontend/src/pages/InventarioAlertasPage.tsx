import React, { useEffect, useState } from 'react';
import { api } from '../services/api';
import { LoteInventario } from '../types';

interface AlertasResponse {
  alertas_vencimiento: LoteInventario[];
  alertas_stock_minimo: LoteInventario[];
}

export const InventarioAlertasPage: React.FC = () => {
  const [vencimientos, setVencimientos] = useState<LoteInventario[]>([]);
  const [stockMinimo, setStockMinimo] = useState<LoteInventario[]>([]);
  const [lotes, setLotes] = useState<LoteInventario[]>([]);
  const [filtroBodega, setFiltroBodega] = useState<string>('');
  const [busqueda, setBusqueda] = useState<string>('');

  useEffect(() => {
    // 1. Cargar Alertas
    api.get<AlertasResponse>('/inventario/alertas').then((res) => {
      setVencimientos(res.data.alertas_vencimiento || []);
      setStockMinimo(res.data.alertas_stock_minimo || []);
    }).catch((err) => console.error(err));

    // 2. Cargar Lotes detallados para la tabla inferior
    
    api.get<LoteInventario[]>('/lotes').then((res) => {
      setLotes(Array.isArray(res.data) ? res.data : []);
    }).catch(() => {
      // Si falla la ruta /lotes, intentar con la ruta /inventario/lotes
      api.get<any[]>('/inventario/lotes').then((res) => {
        setLotes(Array.isArray(res.data) ? res.data : []);
      }).catch((err) => console.error(err));
    });
  }, []);

  const lotesFiltrados = lotes.filter((item) => {
    const bodegaId = item.bodega_id || item.bodega?.id;
    const coincideBodega = filtroBodega === '' || (bodegaId && bodegaId.toString() === filtroBodega);
    const nombreProd = item.producto?.nombre || '';
    const loteCod = item.lote || '';
    const coincideTexto = 
      loteCod.toLowerCase().includes(busqueda.toLowerCase()) ||
      nombreProd.toLowerCase().includes(busqueda.toLowerCase());

    return coincideBodega && coincideTexto;
  });

  return (
    <div className="p-6 max-w-7xl mx-auto space-y-8">
      {/* Sección de Alertas (RN-11) */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="bg-white p-6 rounded-2xl shadow-xl border-l-4 border-red-500 space-y-4">
          <h3 className="text-lg font-extrabold text-red-700 flex items-center gap-2">
             Próximos Vencimientos 
          </h3>
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead>
                <tr className="border-b bg-red-50 text-red-900">
                  <th className="p-2">Medicamento</th>
                  <th className="p-2">Lote</th>
                  <th className="p-2">Fecha Vence</th>
                  <th className="p-2">Cant.</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-red-100">
                {vencimientos.map((item) => (
                  <tr key={`v-${item.id}`}>
                    <td className="p-2 font-semibold">{item.producto?.nombre}</td>
                    <td className="p-2 font-mono">{item.lote}</td>
                    <td className="p-2 font-bold text-red-600">{item.fecha_vencimiento}</td>
                    <td className="p-2">{item.cantidad}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>

        <div className="bg-white p-6 rounded-2xl shadow-xl border-l-4 border-amber-500 space-y-4">
          <h3 className="text-lg font-extrabold text-amber-700 flex items-center gap-2">
             Stock Crítico
          </h3>
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead>
                <tr className="border-b bg-amber-50 text-amber-900">
                  <th className="p-2">Medicamento</th>
                  <th className="p-2">Lote</th>
                  <th className="p-2">Stock Actual</th>
                  <th className="p-2">Mínimo</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-amber-100">
                {stockMinimo.map((item) => (
                  <tr key={`s-${item.id}`}>
                    <td className="p-2 font-semibold">{item.producto?.nombre}</td>
                    <td className="p-2 font-mono">{item.lote}</td>
                    <td className="p-2 font-bold text-amber-600">{item.cantidad}</td>
                    <td className="p-2 text-gray-500">{item.stock_minimo}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {/* Consulta General de Inventario por Lotes y Bodegas con Filtros */}
      <div className="bg-white p-6 rounded-2xl shadow-xl space-y-4">
        <h2 className="text-2xl font-extrabold text-gray-900">Inventario Detallado por Bodega y Lote</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <select
            value={filtroBodega}
            onChange={(e) => setFiltroBodega(e.target.value)}
            className="border border-gray-300 p-3 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
          >
            <option value="">Todas las Bodegas</option>
            <option value="1">Farmacia Central (1)</option>
            <option value="2">Farmacia Urgencias (2)</option>
            <option value="3">Bodega Hospitalización (3)</option>
          </select>
          <input
            type="text"
            placeholder="Buscar por medicamento o lote..."
            value={busqueda}
            onChange={(e) => setBusqueda(e.target.value)}
            className="border border-gray-300 p-3 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
          />
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="border-b bg-gray-50 text-xs font-bold text-gray-600 uppercase">
                <th className="p-3">Bodega</th>
                <th className="p-3">Medicamento (CUM)</th>
                <th className="p-3">Lote</th>
                <th className="p-3">Vencimiento</th>
                <th className="p-3">Cantidad</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100 text-sm">
              {lotesFiltrados.length > 0 ? (
                lotesFiltrados.map((item) => (
                  <tr key={item.id} className="hover:bg-gray-50">
                    <td className="p-3 font-semibold">{item.bodega?.nombre || `Bodega #${item.bodega_id}`}</td>
                    <td className="p-3">
                      <div className="font-bold">{item.producto?.nombre}</div>
                      <div className="text-xs font-mono text-gray-400">{item.producto?.codigo_cum}</div>
                    </td>
                    <td className="p-3 font-mono text-xs text-indigo-600 font-bold">{item.lote}</td>
                    <td className="p-3 text-gray-600">{item.fecha_vencimiento}</td>
                    <td className="p-3 font-bold text-blue-600">{item.cantidad}</td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan={5} className="p-6 text-center text-gray-500">
                    No se encontraron lotes registrados para mostrar en la tabla.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};