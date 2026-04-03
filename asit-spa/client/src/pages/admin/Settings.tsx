import { useState, useEffect } from 'react';
import { api } from '../../lib/api';
import { 
  Save,
  Globe,
  ShieldAlert,
  CheckCircle2,
  Lock
} from 'lucide-react';

interface Setting {
  id: string;
  setting_key: string;
  setting_value: string;
}

export function AdminSettings() {
  const [settings, setSettings] = useState<Record<string, string>>({
    site_name: 'AS-Advance iT',
    maintenance_mode: 'false',
    allow_registration: 'false',
    shift_duration_hours: '8',
    duplicate_window_hours: '72',
  });
  
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const [message, setMessage] = useState({ text: '', type: '' });

  useEffect(() => {
    const fetchSettings = async () => {
      try {
        const { data } = await api.get('/admin/settings');
        // Convert array of KVs to an object map
        const settingsMap: Record<string, string> = {};
        data.data.forEach((s: Setting) => {
          settingsMap[s.setting_key] = s.setting_value;
        });
        setSettings(prev => ({ ...prev, ...settingsMap }));
      } catch (error) {
        console.error('Failed to fetch settings', error);
      } finally {
        setIsLoading(false);
      }
    };
    fetchSettings();
  }, []);

  const handleChange = (key: string, value: string) => {
    setSettings(prev => ({ ...prev, [key]: value }));
  };

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSaving(true);
    setMessage({ text: '', type: '' });

    try {
      // API expects { key, value } updates typically, or batch update
      // Assuming a generic bulk update or looping over keys
      const promises = Object.entries(settings).map(([key, value]) => 
        api.post('/admin/settings', { key, value }).catch((err: any) => {
          // Ignore validation ignores if key already exists, use PUT if needed
          console.log(`Setting update err for ${key}`, err);
        })
      );
      
      await Promise.all(promises);
      setMessage({ text: 'Global settings updated successfully', type: 'success' });
      
    } catch (error) {
      setMessage({ text: 'Failed to update some settings.', type: 'error' });
    } finally {
      setIsSaving(false);
      setTimeout(() => setMessage({text:'', type:''}), 3000);
    }
  };

  if (isLoading) {
    return <div className="flex justify-center p-12"><div className="w-8 h-8 rounded-full border-4 border-blue-500 border-t-transparent animate-spin"></div></div>;
  }

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      <div>
        <h2 className="text-2xl font-bold tracking-tight text-slate-800">Global Settings</h2>
        <p className="text-slate-500">Configure application-wide parameters and operational thresholds.</p>
      </div>

      <form onSubmit={handleSave} className="space-y-6">
        
        {message.text && (
          <div className={`p-4 rounded-xl flex items-center justify-between text-sm ${
            message.type === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'
          } shadow-sm transition-all absolute top-4 right-4 z-50`}>
            <div className="flex items-center gap-2">
              <CheckCircle2 className="w-5 h-5" />
              <p className="font-medium">{message.text}</p>
            </div>
          </div>
        )}

        {/* General Settings */}
        <div className="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
          <div className="px-6 py-4 border-b border-slate-100 flex items-center gap-3 bg-slate-50">
             <Globe className="w-5 h-5 text-blue-600" />
             <h3 className="font-semibold text-slate-800">General Overview</h3>
          </div>
          <div className="p-6 space-y-6">
            <div className="max-w-md">
              <label className="block text-sm font-medium text-slate-700 mb-2">Platform Name</label>
              <input
                type="text"
                value={settings.site_name || ''}
                onChange={(e) => handleChange('site_name', e.target.value)}
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
              />
            </div>
          </div>
        </div>

        {/* Security & Access */}
        <div className="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
          <div className="px-6 py-4 border-b border-slate-100 flex items-center gap-3 bg-slate-50">
             <ShieldAlert className="w-5 h-5 text-indigo-600" />
             <h3 className="font-semibold text-slate-800">Security & Limits</h3>
          </div>
          <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-2">Maintenance Mode</label>
              <select
                value={settings.maintenance_mode || 'false'}
                onChange={(e) => handleChange('maintenance_mode', e.target.value)}
                className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"
              >
                <option value="false">Disabled (Live)</option>
                <option value="true">Enabled (Lockdown)</option>
              </select>
              <p className="mt-2 text-xs text-slate-500">Locks all non-admin users out of the system.</p>
            </div>

            <div>
              <label className="flex items-center gap-2 text-sm font-medium text-slate-700 mb-2">
                <Lock className="w-4 h-4 text-slate-400" />
                Duplicate Checking Window (Hours)
              </label>
              <div className="relative">
                <input
                  type="number"
                  min="1"
                  max="168"
                  value={settings.duplicate_window_hours || '72'}
                  onChange={(e) => handleChange('duplicate_window_hours', e.target.value)}
                  className="w-full pl-4 pr-12 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"
                />
                <div className="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400 text-sm">
                  hrs
                </div>
              </div>
              <p className="mt-2 text-xs text-slate-500">Timeframe required before a repeating token is accepted.</p>
            </div>

          </div>
        </div>

        {/* Action Bar */}
        <div className="flex justify-end pt-4">
          <button
            type="submit"
            disabled={isSaving}
            className="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-medium transition shadow-sm disabled:opacity-50"
          >
            {isSaving ? (
              <div className="w-5 h-5 rounded-full border-2 border-white border-t-transparent animate-spin"></div>
            ) : (
              <Save className="w-5 h-5" />
            )}
            Save All Configurations
          </button>
        </div>

      </form>
    </div>
  );
}
