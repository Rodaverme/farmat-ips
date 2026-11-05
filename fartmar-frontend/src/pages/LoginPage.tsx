import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { api } from '../services/api';

interface LoginResponse {
  token: string;
  user: {
    id: number;
    name: string;
    email: string;
    role: string;
  };
}

export const LoginPage: React.FC = () => {
  const [email, setEmail] = useState<string>('regente@farmart.com');
  const [password, setPassword] = useState<string>('password123');
  const [loading, setLoading] = useState<boolean>(false);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);

  const navigate = useNavigate();

  const handleLogin = async (e: React.FormEvent<HTMLFormElement>): Promise<void> => {
  e.preventDefault();
  setLoading(true);
  setErrorMsg(null);

  try {
    const res = await api.post('/login', { email, password });
    
    // Extraer token soportando diferentes nombres de propiedad de Laravel Sanctum
    const token = res.data.token || res.data.access_token || res.data.plainTextToken;

    if (!token) {
      throw new Error('El backend no devolvió un token válido.');
    }

    // Guardar token y usuario en localStorage
    localStorage.setItem('token', token);
    localStorage.setItem('user', JSON.stringify(res.data.user));

    navigate('/dispensacion');
  } catch (err: unknown) {
    const error = err as { response?: { data?: { message?: string } }; message?: string };
    setErrorMsg(error.response?.data?.message || error.message || 'Error en las credenciales.');
  } finally {
    setLoading(false);
  }
};

  return (
    <div className="min-h-screen flex items-center justify-center bg-slate-900 p-4">
      <div className="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md space-y-6">
        <div className="text-center">
          <h1 className="text-3xl font-extrabold text-blue-600">Farmart IPS</h1>
          <p className="text-sm text-gray-500 mt-1">Sistema de Dispensación e Inventarios</p>
        </div>

        {errorMsg && (
          <div className="p-3 bg-red-100 border border-red-400 text-red-700 text-sm rounded-lg">
            {errorMsg}
          </div>
        )}

        <form onSubmit={handleLogin} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
            <input
              type="email"
              value={email}
              onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEmail(e.target.value)}
              className="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
              required
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
            <input
              type="password"
              value={password}
              onChange={(e: React.ChangeEvent<HTMLInputElement>) => setPassword(e.target.value)}
              className="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
              required
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className={`w-full py-3 rounded-lg text-white font-bold transition-colors ${
              loading ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'
            }`}
          >
            {loading ? 'Iniciando sesión...' : 'Ingresar al Sistema'}
          </button>
        </form>

        <div className="border-t pt-4 text-xs text-gray-400 text-center space-y-1">
          <p>Credenciales de prueba sugeridas:</p>
          <p><span className="font-semibold">Regente:</span> regente@farmart.com | password123</p>
          <p><span className="font-semibold">Auxiliar:</span> aux1@farmart.com | password123</p>
        </div>
      </div>
    </div>
  );
};