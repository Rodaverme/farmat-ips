import React, { useState } from 'react';
import { api } from '../services/api';
import { Prescripcion } from '../types';

export const DispensacionPage: React.FC = () => {
  const [prescripcionId, setPrescripcionId] = useState<string>('1');
  const [bodegaId, setBodegaId] = useState<number>(1);
  const [cantidad, setCantidad] = useState<number>(5);
  const [prescripcion, setPrescripcion] = useState<Prescripcion | null>(null);

  // Segunda firma (RN-05)
  const [regenteEmail, setRegenteEmail] = useState<string>('regente@farmart.com');
  const [regentePassword, setRegentePassword] = useState<string>('password');

  const [loading, setLoading] = useState<boolean>(false);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);
  const [successMsg, setSuccessMsg] = useState<string | null>(null);

  const buscarPrescripcion = async (): Promise<void> => {
    if (!prescripcionId) return;
    setErrorMsg(null);
    setSuccessMsg(null);
    try {
      const res = await api.get<Prescripcion>(`/prescripciones/${prescripcionId}`);
      setPrescripcion(res.data);
    } catch (err: unknown) {
      const error = err as { response?: { data?: { message?: string } } };
      setErrorMsg(error.response?.data?.message || 'Prescripción no encontrada.');
      setPrescripcion(null);
    }
  };

  const handleSubmitDispensacion = async (e: React.FormEvent<HTMLFormElement>): Promise<void> => {
    e.preventDefault();
    if (!prescripcion || loading) return;

    setLoading(true);
    setErrorMsg(null);
    setSuccessMsg(null);

    const idempotencyKey = crypto.randomUUID(); // RN-09 Idempotencia

    const payload: Record<string, unknown> = {
      idempotency_key: idempotencyKey,
      prescripcion_id: prescripcion.id,
      bodega_id: Number(bodegaId),
      cantidad: Number(cantidad),
    };

    if (prescripcion.producto.es_control_especial) {
      payload.regente_email = regenteEmail;
      payload.regente_password = regentePassword;
    }

    try {
      const res = await api.post<{ message: string }>('/dispensaciones', payload);
      setSuccessMsg(res.data.message || 'Dispensación registrada exitosamente por orden FEFO.');
      setPrescripcion(null);
      setPrescripcionId('');
    } catch (err: unknown) {
      const error = err as { response?: { data?: { message?: string } } };
      setErrorMsg(error.response?.data?.message || 'Error al procesar la dispensación (Revise stock o lotes).');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="p-6 max-w-4xl mx-auto bg-white rounded-2xl shadow-xl space-y-6">
      <div className="border-b pb-4">
        <h2 className="text-2xl font-extrabold text-gray-900">Módulo de Dispensación (FEFO)</h2>
        <p className="text-sm text-gray-500">Busque la prescripción médica para iniciar la entrega automatizada de lotes.</p>
      </div>

      <div className="flex gap-3">
        <input
          type="number"
          placeholder="ID Prescripción (Ej: 1, 2)"
          value={prescripcionId}
          onChange={(e: React.ChangeEvent<HTMLInputElement>) => setPrescripcionId(e.target.value)}
          className="border border-gray-300 p-3 rounded-lg w-full focus:ring-2 focus:ring-blue-500 focus:outline-none"
        />
        <button
          type="button"
          onClick={buscarPrescripcion}
          className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-bold transition-colors whitespace-nowrap"
        >
          Buscar Prescripción
        </button>
      </div>

      {errorMsg && <div className="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm font-medium">{errorMsg}</div>}
      {successMsg && <div className="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-medium">{successMsg}</div>}

      {prescripcion && (
        <form onSubmit={handleSubmitDispensacion} className="space-y-6 border-t pt-6">
          <div className="bg-slate-50 p-5 rounded-xl border border-slate-200 space-y-2">
            <p className="text-sm"><strong>Paciente:</strong> {prescripcion.paciente.nombre} {prescripcion.paciente.apellido} (Doc: {prescripcion.paciente.numero_documento})</p>
            <p className="text-sm"><strong>Medicamento CUM:</strong> {prescripcion.producto.nombre} <span className="font-mono text-xs bg-slate-200 px-2 py-0.5 rounded">{prescripcion.producto.codigo_cum}</span></p>
            <p className="text-sm"><strong>Diagnóstico:</strong> {prescripcion.diagnostico}</p>
            <p className="text-sm">
              <strong>Control Especial (RN-05):</strong>{' '}
              {prescripcion.producto.es_control_especial ? (
                <span className="text-red-700 font-bold bg-red-100 px-2.5 py-0.5 rounded-full text-xs">
                  ⚠️ SÍ (Requiere autorización de 2do Regente)
                </span>
              ) : (
                <span className="text-green-700 font-semibold">NO</span>
              )}
            </p>
            <p className="text-sm font-semibold text-blue-800">
              Pendiente por dispensar: {prescripcion.cantidad_prescrita - prescripcion.cantidad_dispensada} unidades
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Bodega de Despacho:</label>
              <select
                value={bodegaId}
                onChange={(e: React.ChangeEvent<HTMLSelectElement>) => setBodegaId(Number(e.target.value))}
                className="border border-gray-300 p-3 rounded-lg w-full focus:ring-2 focus:ring-blue-500 focus:outline-none"
              >
                <option value={1}>Farmacia Central (1)</option>
                <option value={2}>Farmacia Urgencias (2)</option>
                <option value={3}>Bodega Hospitalización (3)</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Cantidad a Dispensar:</label>
              <input
                type="number"
                min="1"
                max={prescripcion.cantidad_prescrita - prescripcion.cantidad_dispensada}
                value={cantidad}
                onChange={(e: React.ChangeEvent<HTMLInputElement>) => setCantidad(Number(e.target.value))}
                className="border border-gray-300 p-3 rounded-lg w-full focus:ring-2 focus:ring-blue-500 focus:outline-none"
                required
              />
            </div>
          </div>

          {prescripcion.producto.es_control_especial && (
            <div className="p-5 bg-amber-50 border border-amber-200 rounded-xl space-y-4">
              <h4 className="font-bold text-amber-900 text-sm">Autenticación de Segunda Firma (Regente de Farmacia)</h4>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-amber-800 mb-1">Correo Electrónico:</label>
                  <input
                    type="email"
                    value={regenteEmail}
                    onChange={(e: React.ChangeEvent<HTMLInputElement>) => setRegenteEmail(e.target.value)}
                    className="border border-amber-300 p-2.5 rounded-lg w-full text-sm bg-white"
                    required
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-amber-800 mb-1">Contraseña:</label>
                  <input
                    type="password"
                    value={regentePassword}
                    onChange={(e: React.ChangeEvent<HTMLInputElement>) => setRegentePassword(e.target.value)}
                    className="border border-amber-300 p-2.5 rounded-lg w-full text-sm bg-white"
                    required
                  />
                </div>
              </div>
            </div>
          )}

          <button
            type="submit"
            disabled={loading}
            className={`w-full py-3.5 rounded-xl text-white font-bold transition-all shadow-lg ${
              loading ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700 hover:shadow-green-200'
            }`}
          >
            {loading ? 'Procesando algoritmo FEFO y stock...' : 'Confirmar y Ejecutar Dispensación'}
          </button>
        </form>
      )}
    </div>
  );
};