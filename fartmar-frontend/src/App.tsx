import React from 'react';
import { BrowserRouter, Routes, Route, Link, Navigate, useNavigate } from 'react-router-dom';
import { LoginPage } from './pages/LoginPage';
import { DispensacionPage } from './pages/DispensacionPage';
import { TrasladosPage } from './pages/TrasladosPage';
import { InventarioAlertasPage } from './pages/InventarioAlertasPage';
import { KardexPage } from './pages/KardexPage';
import { api } from './services/api';


const Header: React.FC = () => {
  const navigate = useNavigate();
  const userJson = localStorage.getItem('user');
  const user = userJson ? JSON.parse(userJson) : null;

  const handleLogout = async () => {
    try {
      await api.post('/logout');
    } catch {
      // Ignorar errores en logout API
    } finally {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      navigate('/login');
    }
  };

  if (!user) return null;

  return (
    <header className="bg-slate-900 text-white shadow-md">
      <div className="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <div className="flex items-center space-x-3">
          <h1 className="text-xl font-bold text-blue-400">Farmart IPS</h1>
          <span className="bg-slate-800 text-blue-300 text-xs px-2.5 py-1 rounded-full font-mono border border-slate-700">
            {user.role}
          </span>
        </div>

        <nav className="flex space-x-6 text-sm font-medium">
          <Link to="/dispensacion" className="hover:text-blue-300 transition-colors">
            Dispensación
          </Link>
          <Link to="/traslados" className="hover:text-blue-300 transition-colors">
            Traslados
          </Link>
          
          <Link to="/inventario" className="hover:text-blue-300 transition-colors">
            Inventario & Alertas
          </Link>
          
          
          <Link to="/kardex" className="hover:text-blue-300 transition-colors">
            Kardex
          </Link>
        </nav>

        <div className="flex items-center space-x-4">
          <span className="text-sm text-gray-300">{user.name}</span>
          <button
            onClick={handleLogout}
            className="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
          >
            Cerrar Sesión
          </button>
        </div>
      </div>
    </header>
  );
};

const ProtectedRoute: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const token = localStorage.getItem('token');
  if (!token) {
    return <Navigate to="/login" replace />;
  }
  return <>{children}</>;
};

export const App: React.FC = () => {
  return (
    <BrowserRouter>
      <div className="min-h-screen bg-gray-100 flex flex-col">
        <Header />
        <main className="flex-1 py-8">
          <Routes>
            <Route path="/login" element={<LoginPage />} />
            <Route
              path="/dispensacion"
              element={
                <ProtectedRoute>
                  <DispensacionPage />
                </ProtectedRoute>
              }
            />
            <Route
              path="/traslados"
              element={
                <ProtectedRoute>
                  <TrasladosPage />
                </ProtectedRoute>
              }
            />
            <Route
              path="/inventario"
              element={
                <ProtectedRoute>
                  <InventarioAlertasPage />
                </ProtectedRoute>
              }
            />

          


            <Route
              path="/kardex"
              element={
                <ProtectedRoute>
                  <KardexPage />
                </ProtectedRoute>
              }
            />
            <Route path="*" element={<Navigate to="/dispensacion" replace />} />
          </Routes>
        </main>
      </div>
    </BrowserRouter>
  );
};

export default App;