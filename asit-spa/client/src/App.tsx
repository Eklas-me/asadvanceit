import { 
  BrowserRouter, 
  Routes, 
  Route, 
  Navigate 
} from 'react-router-dom';
import { Toaster } from 'react-hot-toast';
import { AuthLayout } from './layouts/AuthLayout';
import { AdminLayout } from './layouts/AdminLayout';
import { UserLayout } from './layouts/UserLayout';
import { Login } from './pages/auth/Login';
import { ForgotPassword } from './pages/auth/ForgotPassword';
import { AdminDashboard } from './pages/admin/Dashboard';
import { AdminWorkers } from './pages/admin/Workers';
import { AdminAddWorker } from './pages/admin/AddWorker';
import { AdminEditWorker } from './pages/admin/EditWorker';
import { AdminTokens } from './pages/admin/Tokens';
import { UserDashboard } from './pages/user/Dashboard';
import { UserTokens } from './pages/user/Tokens';
import { UserProfile } from './pages/user/Profile';
import { UserSheets } from './pages/user/Sheets';
import { SheetViewer } from './pages/user/SheetViewer';
import { AdminSheets } from './pages/admin/Sheets';
import { ChatApp } from './pages/common/Chat';
import { Notifications } from './pages/common/Notifications';
import { DuplicateChecker } from './pages/common/DuplicateChecker';
import { AdminMonitoring } from './pages/admin/Monitoring';
import { AdminSettings } from './pages/admin/Settings';

function App() {
  return (
    <BrowserRouter>
      <Toaster position="top-right" />
      <Routes>
        {/* Public / Auth Routes */}
        <Route element={<AuthLayout />}>
          <Route path="/login" element={<Login />} />
          <Route path="/forgot-password" element={<ForgotPassword />} />
          <Route path="/" element={<Navigate to="/login" replace />} />
        </Route>

        {/* Admin Routes */}
        <Route path="/admin" element={<AdminLayout />}>
          <Route path="dashboard" element={<AdminDashboard />} />
          <Route path="workers" element={<AdminWorkers />} />
          <Route path="workers/add" element={<AdminAddWorker />} />
          <Route path="workers/edit/:id" element={<AdminEditWorker />} />
          <Route path="tokens" element={<AdminTokens />} />
          <Route path="sheets" element={<AdminSheets />} />
          <Route path="sheets/view/:slug" element={<SheetViewer />} />
          <Route path="monitoring" element={<AdminMonitoring />} />
          <Route path="chat" element={<ChatApp />} />
          <Route path="settings" element={<AdminSettings />} />
          <Route path="notifications" element={<Notifications />} />
          <Route path="duplicate-checker" element={<DuplicateChecker />} />
          <Route path="" element={<Navigate to="/admin/dashboard" replace />} />
        </Route>

        {/* User Routes */}
        <Route path="/user" element={<UserLayout />}>
          <Route path="dashboard" element={<UserDashboard />} />
          <Route path="tokens" element={<UserTokens />} />
          <Route path="chat" element={<ChatApp />} />
          <Route path="sheets" element={<UserSheets />} />
          <Route path="sheets/:slug" element={<SheetViewer />} />
          <Route path="profile" element={<UserProfile />} />
          <Route path="notifications" element={<Notifications />} />
          <Route path="duplicate-checker" element={<DuplicateChecker />} />
          <Route path="" element={<Navigate to="/user/dashboard" replace />} />
        </Route>

        {/* Fallback */}
        <Route path="*" element={<Navigate to="/login" replace />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
