import { useState, useEffect } from 'react';
import { api } from '../../lib/api';
import { 
  UserPlus, 
  Search, 
  Edit2, 
  Trash2, 
  XCircle,
  AlertTriangle,
  Users as UsersIcon,
  ShieldAlert,
  UserX,
  History,
  ShieldCheck
} from 'lucide-react';
import { format } from 'date-fns';
import { useLocation, useNavigate } from 'react-router-dom';

interface Worker {
  id: string;
  name: string;
  email: string;
  phone: string;
  role: string;
  shift: string;
  status: string;
  last_seen: string | null;
  created_at: string;
  needs_password_upgrade: boolean;
  gender: 'male' | 'female' | null;
}

interface Stats {
  total: number;
  active: number;
  suspended: number;
  rejected: number;
  legacy: number;
}

export function AdminWorkers() {
  const navigate = useNavigate();
  const location = useLocation();
  const queryParams = new URLSearchParams(location.search);
  const statusFilter = queryParams.get('status');

  const [workers, setWorkers] = useState<Worker[]>([]);
  const [stats, setStats] = useState<Stats | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [activeTab, setActiveTab] = useState<'worker' | 'admin'>('worker');
  const [selectedWorkers, setSelectedWorkers] = useState<string[]>([]);

  const fetchWorkers = async () => {
    setIsLoading(true);
    try {
      const [{ data: workerData }, { data: statsData }] = await Promise.all([
        api.get('/admin/workers', { params: { status: statusFilter } }),
        api.get('/admin/workers/stats')
      ]);
      setWorkers(workerData.data || []);
      setStats(statsData.data);
    } catch (error) {
      console.error('Failed to fetch workers', error);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchWorkers();
  }, [statusFilter]);

  const handleStatusToggle = async (id: string, currentStatus: string) => {
    try {
      const newStatus = currentStatus === 'active' ? 'suspended' : 'active';
      await api.patch(`/admin/workers/${id}/status`, { status: newStatus });
      setWorkers(workers.map(w => w.id === id ? { ...w, status: newStatus } : w));
      // Refresh stats
      const { data } = await api.get('/admin/workers/stats');
      setStats(data.data);
    } catch (error) {
      console.error('Failed to update status', error);
    }
  };

  const filteredWorkers = workers.filter(w => {
    const matchesSearch = (
      w.name?.toLowerCase().includes(searchTerm.toLowerCase()) || 
      w.email?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      w.phone?.toLowerCase().includes(searchTerm.toLowerCase())
    );
    const matchesTab = activeTab === 'admin' ? w.role === 'admin' : w.role !== 'admin';
    return matchesSearch && matchesTab;
  });

  const toggleSelectAll = () => {
    if (selectedWorkers.length === filteredWorkers.length) {
      setSelectedWorkers([]);
    } else {
      setSelectedWorkers(filteredWorkers.map(w => w.id));
    }
  };

  const toggleSelect = (id: string) => {
    setSelectedWorkers(prev => 
      prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id]
    );
  };

  const handleBulkStatus = async (status: 'active' | 'suspended') => {
    if (selectedWorkers.length === 0) return;
    try {
      await api.patch('/admin/workers/bulk/status', { ids: selectedWorkers, status });
      setWorkers(workers.map(w => selectedWorkers.includes(w.id) ? { ...w, status } : w));
      setSelectedWorkers([]);
      // Refresh stats
      const { data } = await api.get('/admin/workers/stats');
      setStats(data.data);
    } catch (error) {
      console.error('Bulk status update failed', error);
      alert('Failed to update multiple worker statuses.');
    }
  };

  const handleBulkDelete = async () => {
    if (selectedWorkers.length === 0) return;
    if (!confirm(`Are you sure you want to delete ${selectedWorkers.length} workers? This action cannot be undone.`)) return;

    try {
      await api.post('/admin/workers/bulk/delete', { ids: selectedWorkers });
      setWorkers(workers.filter(w => !selectedWorkers.includes(w.id)));
      setSelectedWorkers([]);
      // Refresh stats
      const { data } = await api.get('/admin/workers/stats');
      setStats(data.data);
    } catch (error) {
      console.error('Bulk delete failed', error);
      alert('Failed to delete selected workers.');
    }
  };

  return (
    <div className="space-y-8 animate-in fade-in duration-500">
      {/* Header Section */}
      <div className="flex justify-between items-start flex-wrap gap-6">
        <div>
          <h2 className="text-3xl font-extrabold tracking-tight text-slate-900 capitalize">
            {statusFilter ? `${statusFilter} Workers` : 'Manage Workers'}
          </h2>
          <p className="text-slate-500 mt-1">
            Oversee your workforce, track password security, and manage access levels.
          </p>
        </div>
        <button 
          onClick={() => navigate('/admin/workers/add')}
          className="flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/30 border border-blue-500 active:scale-95"
        >
          <UserPlus className="w-5 h-5" />
          Add New Worker
        </button>
      </div>

      {/* Legacy Password Warning */}
      {stats && stats.legacy > 0 && (
        <div className="bg-amber-50 border-l-4 border-amber-400 p-5 rounded-r-2xl shadow-sm flex items-start gap-4">
          <AlertTriangle className="w-6 h-6 text-amber-500 shrink-0 mt-0.5" />
          <div>
            <h4 className="text-amber-800 font-bold text-base">Attention Needed</h4>
            <p className="text-amber-700 text-sm mt-1">
              Currently, <span className="font-extrabold">{stats.legacy}</span> workers are still using legacy (MD5) passwords. 
              They will automatically be prompted to upgrade upon their next login.
            </p>
          </div>
        </div>
      )}

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white p-6 rounded-3xl shadow-sm border border-emerald-100 relative overflow-hidden group">
          <div className="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <UsersIcon className="w-16 h-16 text-emerald-600" />
          </div>
          <div className="relative z-10 flex items-center gap-4">
            <div className="p-3 bg-emerald-50 rounded-2xl text-emerald-600">
              <UsersIcon className="w-6 h-6" />
            </div>
            <div>
              <p className="text-sm font-semibold text-slate-500">Total Active Workers</p>
              <h3 className="text-3xl font-black text-slate-900">{stats?.active || 0}</h3>
            </div>
          </div>
          <div className="mt-4 h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
            <div className="h-full bg-emerald-500 rounded-full" style={{ width: stats ? `${(stats.active/stats.total)*100}%` : '0%' }}></div>
          </div>
        </div>

        <div className="bg-white p-6 rounded-3xl shadow-sm border border-amber-100 relative overflow-hidden group">
          <div className="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <ShieldAlert className="w-16 h-16 text-amber-600" />
          </div>
          <div className="relative z-10 flex items-center gap-4">
            <div className="p-3 bg-amber-50 rounded-2xl text-amber-600">
              <ShieldAlert className="w-6 h-6" />
            </div>
            <div>
              <p className="text-sm font-semibold text-slate-500">Suspended Workers</p>
              <h3 className="text-3xl font-black text-slate-900">{stats?.suspended || 0}</h3>
            </div>
          </div>
          <div className="mt-4 h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
            <div className="h-full bg-amber-500 rounded-full" style={{ width: stats ? `${(stats.suspended/stats.total)*100}%` : '0%' }}></div>
          </div>
        </div>

        <div className="bg-white p-6 rounded-3xl shadow-sm border border-rose-100 relative overflow-hidden group">
          <div className="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <UserX className="w-16 h-16 text-rose-600" />
          </div>
          <div className="relative z-10 flex items-center gap-4">
            <div className="p-3 bg-rose-50 rounded-2xl text-rose-600">
              <UserX className="w-6 h-6" />
            </div>
            <div>
              <p className="text-sm font-semibold text-slate-500">Rejected Workers</p>
              <h3 className="text-3xl font-black text-slate-900">{stats?.rejected || 0}</h3>
            </div>
          </div>
          <div className="mt-4 h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
            <div className="h-full bg-rose-500 rounded-full" style={{ width: stats ? `${(stats.rejected/stats.total)*100}%` : '0%' }}></div>
          </div>
        </div>
      </div>

      {/* Main Content Area */}
      <div className="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
        {/* Tabs and Search Bar */}
        <div className="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div className="flex bg-slate-50 p-1.5 rounded-2xl w-fit">
            <button
              onClick={() => setActiveTab('worker')}
              className={`px-6 py-2.5 rounded-xl text-sm font-bold transition-all ${
                activeTab === 'worker' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'
              }`}
            >
              Worker List
            </button>
            <button
              onClick={() => setActiveTab('admin')}
              className={`px-6 py-2.5 rounded-xl text-sm font-bold transition-all ${
                activeTab === 'admin' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'
              }`}
            >
              Admin List
            </button>
          </div>

          <div className="relative w-full max-w-sm">
            <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <Search className="h-4 w-4 text-slate-400" />
            </div>
            <input
              type="text"
              className="block w-full pl-11 pr-4 bg-slate-50 border border-slate-200 rounded-2xl py-3 text-sm outline-none text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/10 focus:border-blue-500 transition-all shadow-inner"
              placeholder="Search by name, phone, or email..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
          </div>
        </div>

        {/* Table */}
        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left border-collapse table-fixed">
            <thead className="bg-slate-50/50 text-slate-500 uppercase text-[10px] font-black tracking-widest border-b border-slate-100">
              <tr>
                <th className="px-4 py-4 w-12 text-center">
                  <input 
                    type="checkbox" 
                    checked={selectedWorkers.length === filteredWorkers.length && filteredWorkers.length > 0}
                    onChange={toggleSelectAll}
                    className="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                  />
                </th>
                <th className="px-4 py-4 w-12 text-center">SN</th>
                <th className="px-4 py-4 text-left min-w-[200px]">Worker Details</th>
                <th className="px-4 py-4 w-36">Phone</th>
                <th className="px-4 py-4 w-44 text-left">Shift / Role</th>
                <th className="px-4 py-4 w-32">Joined at</th>
                <th className="px-4 py-4 w-28">Security</th>
                <th className="px-4 py-4 w-28 text-center">Status</th>
                <th className="px-4 py-4 w-32 text-right pr-6">Action</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {isLoading ? (
                <tr>
                  <td colSpan={9} className="px-4 py-24 text-center">
                    <div className="flex flex-col items-center gap-3">
                      <div className="w-10 h-10 rounded-full border-4 border-blue-500 border-t-transparent animate-spin"></div>
                      <p className="text-slate-500 font-medium animate-pulse">Syncing workforce data...</p>
                    </div>
                  </td>
                </tr>
              ) : filteredWorkers.length === 0 ? (
                <tr>
                  <td colSpan={9} className="px-4 py-24 text-center">
                    <div className="flex flex-col items-center gap-3">
                      <div className="p-4 bg-slate-50 rounded-full">
                        <UserX className="w-10 h-10 text-slate-300" />
                      </div>
                      <p className="text-slate-500 text-lg font-bold">No Records Found</p>
                      <p className="text-slate-400 text-sm max-w-[200px]">Modify your search or status filter to see more results.</p>
                    </div>
                  </td>
                </tr>
              ) : (
                filteredWorkers.map((worker, index) => (
                  <tr 
                    key={worker.id} 
                    className={`group hover:bg-slate-50/80 transition-all duration-300 border-b border-slate-50 last:border-0 ${selectedWorkers.includes(worker.id) ? 'bg-blue-50/30' : ''}`}
                  >
                    <td className="px-4 py-4 text-center">
                      <input 
                        type="checkbox" 
                        checked={selectedWorkers.includes(worker.id)}
                        onChange={() => toggleSelect(worker.id)}
                        className="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                      />
                    </td>
                    <td className="px-4 py-4 text-center font-bold text-slate-400">{index + 1}</td>
                    <td className="px-4 py-4">
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 border-2 border-white shadow-sm text-slate-600 flex items-center justify-center font-black text-xs shrink-0 uppercase">
                          {worker.name.substring(0, 2)}
                        </div>
                        <div className="min-w-0 flex-1">
                          <div className="font-bold text-slate-900 group-hover:text-blue-600 transition-colors uppercase truncate text-xs" title={worker.name}>{worker.name}</div>
                          <div className="text-slate-500 text-[10px] font-medium tracking-tight mt-0.5 truncate" title={worker.email}>{worker.email}</div>
                        </div>
                      </div>
                    </td>
                    <td className="px-4 py-4 font-semibold text-slate-700 whitespace-nowrap text-xs">{worker.phone || '---'}</td>
                    <td className="px-4 py-4">
                      <div className="flex flex-col gap-1">
                        <span className="font-bold text-[10px] text-slate-800 uppercase leading-none whitespace-nowrap truncate" title={worker.shift || 'Flexible'}>{worker.shift || 'Flexible'}</span>
                        <div className="flex items-center gap-1.5">
                          <span className="text-[9px] font-black tracking-widest text-slate-400 uppercase leading-none">{worker.role}</span>
                          {worker.gender && (
                            <span className={`w-1.5 h-1.5 rounded-full ${worker.gender === 'female' ? 'bg-pink-400' : 'bg-blue-400'}`} title={worker.gender}></span>
                          )}
                        </div>
                      </div>
                    </td>
                    <td className="px-4 py-4">
                      <div className="flex items-center gap-1.5 text-slate-600 font-bold text-[9px] uppercase whitespace-nowrap">
                        <History className="w-3 h-3 text-slate-400" />
                        {worker.created_at ? format(new Date(worker.created_at), 'MMM dd, yyyy') : '---'}
                      </div>
                    </td>
                    <td className="px-4 py-4">
                      {worker.needs_password_upgrade ? (
                        <span className="inline-flex items-center gap-1 px-2 py-1 bg-rose-50 text-rose-600 rounded-lg text-[9px] font-black uppercase ring-1 ring-rose-200/50 ring-inset">
                          <AlertTriangle className="w-2.5 h-2.5" />
                          Legacy
                        </span>
                      ) : (
                        <span className="inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[9px] font-black uppercase ring-1 ring-emerald-200/50 ring-inset">
                          <ShieldCheck className="w-2.5 h-2.5" />
                          Secure
                        </span>
                      )}
                    </td>
                    <td className="px-4 py-4 text-center">
                      <button 
                        onClick={() => handleStatusToggle(worker.id, worker.status)}
                        className={`inline-flex items-center justify-center min-w-[70px] px-2 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-wider transition-all duration-300 shadow-sm border ${
                          worker.status === 'active' 
                            ? 'bg-emerald-500 text-white border-emerald-400 hover:bg-emerald-600' 
                            : 'bg-amber-500 text-white border-amber-400 hover:bg-amber-600'
                        }`}
                      >
                        {worker.status}
                      </button>
                    </td>
                    <td className="px-4 py-4 text-right whitespace-nowrap pr-6">
                      <div className="flex items-center justify-end gap-1">
                        <button 
                          onClick={() => navigate(`/admin/workers/edit/${worker.id}`)}
                          className="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" 
                          title="Edit Profile"
                        >
                          <Edit2 className="w-3.5 h-3.5" />
                        </button>
                        <button 
                          onClick={() => handleStatusToggle(worker.id, worker.status)}
                          className="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all"
                          title="Toggle Access"
                        >
                          <XCircle className="w-3.5 h-3.5" />
                        </button>
                        <button 
                          onClick={() => { if(confirm('Are you sure?')) api.delete(`/admin/workers/${worker.id}`).then(fetchWorkers) }}
                          className="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" 
                          title="Purge Record"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        {/* Floating Bulk Actions Bar */}
        {selectedWorkers.length > 0 && (
          <div className="fixed bottom-8 left-1/2 -translate-x-1/2 z-50 flex items-center gap-6 px-6 py-4 bg-slate-900/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-800 animate-in slide-in-from-bottom-8 duration-300">
            <span className="font-bold text-white flex items-center gap-3">
              <span className="bg-blue-600 flex items-center justify-center min-w-[32px] h-8 rounded-lg text-sm">{selectedWorkers.length}</span>
              <span>Workers Selected</span>
            </span>
            <div className="w-px h-8 bg-slate-700"></div>
            <div className="flex gap-3">
              <button 
                onClick={() => handleBulkStatus('active')}
                className="px-5 py-2.5 bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500 hover:text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all"
              >
                Approve All
              </button>
              <button 
                onClick={() => handleBulkStatus('suspended')}
                className="px-5 py-2.5 bg-amber-500/10 text-amber-400 hover:bg-amber-500 hover:text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all"
              >
                Suspend All
              </button>
              <button 
                onClick={handleBulkDelete}
                className="px-5 py-2.5 bg-rose-500/10 text-rose-400 hover:bg-rose-500 hover:text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all"
              >
                Delete Selected
              </button>
            </div>
            <button 
              onClick={() => setSelectedWorkers([])}
              className="ml-2 p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all"
              title="Clear Selection"
            >
              <XCircle className="w-5 h-5" />
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
