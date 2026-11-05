import React, { useEffect, useState } from 'react';
import { api } from '../services/api';
import { Traslado } from '../types';

export const TrasladosPage: React.FC = () => {
  const [traslados, setTraslados] = useState<Traslado[]>([]);
  const [loadingAction, setLoadingAction] = useState<number | null>(null);
  const [message, setMessage] = useState<string | null>(null);

  // Modal de Creación
  const [showModal, setShowModal] = useState<boolean>(false);
  const [bodegaOrigenId, setBodegaOrigenId] = useState<number>(1);
  const [bodegaDestinoId, setBodegaDestinoId] = useState<number>(2);
  const [loteId, setLoteId] = useState<number>(1);
  const [cantidad, setCantidad] = useState<number>(10);
  const [observaciones, setObservaciones] = useState<string>('Reabastecimiento urgente de stock');

  const cargarTraslados = async (): Promise<void> => {
    try {
      const res = await api.get<any>('/traslados');
      // Soporta tanto si devuelve un array directo como si viene paginado en .data
      const data = Array.isArray(res.data) ? res.data : (res.data.data || []);
      setTraslados(data);
    } catch (err) {
      console.error('Error al cargar traslados:', err);
    }
  };

  useEffect(() => {
    cargarTraslados();
  }, []);

  const ejecutarAccion = async (traslado: Traslado, accion: 'aprobar' | 'despachar' | 'recibir'): Promise<void> => {
    setLoadingAction(traslado.id);
    setMessage(null);
    try {
      let payload: unknown = {};

      // Si la acción es recibir, construimos el array de detalles requerido por el backend
      if (accion === 'recibir') {
        payload = {
          detalles: traslado.detalles?.map(d => ({
            detalle_id: d.id, // ID del detalle del traslado
            cantidad_recibida: d.cantidad_solicitada // Por defecto recibe la cantidad solicitada (puedes ajustarlo si deseas un input)
          })) || []
        };
      }

      await api.post(`/traslados/${traslado.id}/${accion}`, payload);
      setMessage(`Traslado #${traslado.id} actualizado exitosamente.`);
      cargarTraslados();
    } catch (err: unknown) {
      const error = err as { response?: { data?: { message?: string } } };
      setMessage(`Acción denegada: ${error.response?.data?.message || 'No autorizado o regla violada.'}`);
    } finally {
      setLoadingAction(null);
    }
  };

  const handleCrearTraslado = async (e: React.FormEvent<HTMLFormElement>): Promise<void> => {
    e.preventDefault();
    setMessage(null);

    const payload = {
      bodega_origen_id: Number(bodegaOrigenId),
      bodega_destino_id: Number(bodegaDestinoId),
      observaciones,
      detalles: [
        {
          lote_inventario_id: Number(loteId),
          cantidad_solicitada: Number(cantidad),
        }
      ]
    };

    try {
      await api.post('/traslados', payload);
      setMessage('Traslado solicitado con éxito.');
      setShowModal(false);
      cargarTraslados();
    } catch (err: unknown) {
      const error = err as { response?: { data?: { message?: string } } };
      setMessage(`Error al solicitar traslado: ${error.response?.data?.message || 'Verifique los datos.'}`);
    }
  };

  return (
    <div className="p-6 max-w-7xl mx-auto space-y-6">
      <div className="flex justify-between items-center bg-white p-6 rounded-2xl shadow-xl">
        <div>
          <h2 className="text-2xl font-extrabold text-gray-900">Gestión de Traslados Inter-bodegas</h2>
          <p className="text-sm text-gray-500">Supervise el ciclo de estado y ejecute las aprobaciones según su rol (RN-07, RN-08).</p>
        </div>
        <button
          onClick={() => setShowModal(true)}
          className="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow transition-colors"
        >
          + Solicitar Traslado
        </button>
      </div>

      {message && <div className="p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl text-sm font-medium">{message}</div>}

      {/* Modal Creación */}
      {showModal && (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
          <div className="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-lg space-y-5">
            <h3 className="text-xl font-bold text-gray-900">Nueva Solicitud de Traslado</h3>
            <form onSubmit={handleCrearTraslado} className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">Bodega Origen:</label>
                  <select
                    value={bodegaOrigenId}
                    onChange={(e) => setBodegaOrigenId(Number(e.target.value))}
                    className="border p-2.5 rounded-lg w-full text-sm"
                  >
                    <option value={1}>Farmacia Central (1)</option>
                    <option value={2}>Farmacia Urgencias (2)</option>
                    <option value={3}>Bodega Hospitalización (3)</option>
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">Bodega Destino:</label>
                  <select
                    value={bodegaDestinoId}
                    onChange={(e) => setBodegaDestinoId(Number(e.target.value))}
                    className="border p-2.5 rounded-lg w-full text-sm"
                  >
                    <option value={2}>Farmacia Urgencias (2)</option>
                    <option value={1}>Farmacia Central (1)</option>
                    <option value={3}>Bodega Hospitalización (3)</option>
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">ID Lote Inventario:</label>
                  <input
                    type="number"
                    value={loteId}
                    onChange={(e) => setLoteId(Number(e.target.value))}
                    className="border p-2.5 rounded-lg w-full text-sm"
                    required
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">Cantidad:</label>
                  <input
                    type="number"
                    min="1"
                    value={cantidad}
                    onChange={(e) => setCantidad(Number(e.target.value))}
                    className="border p-2.5 rounded-lg w-full text-sm"
                    required
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">Observaciones:</label>
                <input
                  type="text"
                  value={observaciones}
                  onChange={(e) => setObservaciones(e.target.value)}
                  className="border p-2.5 rounded-lg w-full text-sm"
                />
              </div>

              <div className="flex justify-end space-x-3 pt-3">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-xl text-sm font-bold"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  className="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-xl text-sm font-bold"
                >
                  Crear Solicitud
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Tabla de Traslados */}
      <div className="bg-white rounded-2xl shadow-xl overflow-hidden">
        <table className="w-full text-left border-collapse">
          <thead>
            <tr className="border-b bg-gray-50 text-xs font-bold text-gray-600 uppercase tracking-wider">
              <th className="p-4">ID</th>
              <th className="p-4">Origen / Destino</th>
              <th className="p-4">Estado Actual</th>
              <th className="p-4">Detalles / Ítems</th>
              <th className="p-4">Acciones de Transición</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {traslados.length > 0 ? (
              traslados.map((t) => (
                <tr key={t.id} className="hover:bg-gray-50 text-sm align-top">
                  <td className="p-4 font-mono font-bold">#{t.id}</td>
                  <td className="p-4 text-gray-700">
                    Bodega {t.bodega_origen_id} / Bodega {t.bodega_destino_id}
                    <div className="text-xs text-gray-400 mt-1">{t.observaciones || 'Sin notas'}</div>
                  </td>
                  <td className="p-4">
                    <span className={`px-3 py-1 rounded-full text-xs font-bold ${
                      t.estado === 'RECIBIDO' ? 'bg-green-100 text-green-800' :
                      t.estado === 'EN_TRANSITO' ? 'bg-amber-100 text-amber-800' :
                      t.estado === 'APROBADO' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'
                    }`}>
                      {t.estado}
                    </span>
                  </td>
                  <td className="p-4 text-xs text-gray-600">
                    {t.detalles && t.detalles.length > 0 ? (
                      t.detalles.map((d, idx) => (
                        <div key={idx} className="bg-slate-50 p-1.5 rounded mb-1 border border-slate-200">
                          <span>Lote ID: <strong>{d.lote_inventario_id}</strong></span> | 
                          <span> Solicitado: <strong>{d.cantidad_solicitada}</strong></span>
                        </div>
                      ))
                    ) : (
                      <span className="italic text-gray-400">Sin detalles adjuntos</span>
                    )}
                  </td>
                  <td className="p-4 space-x-2 space-y-1">
                    {t.estado === 'SOLICITADO' && (
                      <button
                        disabled={loadingAction === t.id}
                        onClick={() => ejecutarAccion(t, 'aprobar')}
                        className="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-colors"
                      >
                        Aprobar
                      </button>
                    )}
                    {t.estado === 'APROBADO' && (
                      <button
                        disabled={loadingAction === t.id}
                        onClick={() => ejecutarAccion(t, 'despachar')}
                        className="bg-orange-600 hover:bg-orange-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-colors"
                      >
                        Despachar
                      </button>
                    )}
                    {t.estado === 'EN_TRANSITO' && (
                    <button
                      disabled={loadingAction === t.id}
                      onClick={() => ejecutarAccion(t, 'recibir')}
                      className="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-colors"
                    >
                      Recibir
                    </button>
)}
                  </td>
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan={5} className="p-6 text-center text-gray-500">No hay traslados registrados en la base de datos.</td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
};