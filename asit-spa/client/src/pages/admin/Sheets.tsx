import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../../lib/api';
import { 
  Plus, 
  Search, 
  Table as TableIcon,
  Edit2,
  Trash2,
  Eye,
  EyeOff,
  Clock,
  Globe,
  X,
  Loader2,
  Check,
  Lock,
  Users
} from 'lucide-react';
import { toast } from 'react-hot-toast';

interface GoogleSheet {
  id: string;
  slug: string;
  title: string;
  url: string;
  icon: string;
  permission_type: 'public' | 'shift_based' | 'admin_only' | 'specific_users';
  shift: string | null;
  is_visible: boolean;
  order: number;
}

interface Worker {
  id: string;
  name: string;
  email: string;
}

export function AdminSheets() {
  const [sheets, setSheets] = useState<GoogleSheet[]>([]);
  const [workers, setWorkers] = useState<Worker[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [editingSheet, setEditingSheet] = useState<GoogleSheet | null>(null);

  // Form State
  const [formData, setFormData] = useState({
    title: '',
    url: '',
    icon: 'fas fa-file-excel',
    permission_type: 'public' as GoogleSheet['permission_type'],
    shift: '',
    user_ids: [] as string[]
  });

  const shifts = [
    'Morning 8 Hours',
    'Morning 8 Hours Female',
    'Evening 8 Hours',
    'Night 8 Hours',
    'Day 12 Hours',
    'Night 12 Hours',
    'Office Only'
  ];

  const fetchSheets = async () => {
    try {
      const { data } = await api.get('/admin/sheets');
      setSheets(data);
    } catch (error) {
      toast.error('Failed to load sheets');
    } finally {
      setIsLoading(false);
    }
  };

  const fetchWorkers = async () => {
    try {
      const { data } = await api.get('/admin/workers'); // Reusing existing workers endpoint
      setWorkers(data.data || []);
    } catch (error) {
      console.error('Failed to load workers', error);
    }
  };

  useEffect(() => {
    fetchSheets();
    fetchWorkers();
  }, []);

  const openAddModal = () => {
    setEditingSheet(null);
    setFormData({
      title: '',
      url: '',
      icon: 'fas fa-file-excel',
      permission_type: 'public',
      shift: '',
      user_ids: []
    });
    setIsModalOpen(true);
  };

  const openEditModal = (sheet: GoogleSheet) => {
    setEditingSheet(sheet);
    setFormData({
      title: sheet.title,
      url: sheet.url,
      icon: sheet.icon,
      permission_type: sheet.permission_type,
      shift: sheet.shift || '',
      user_ids: [] // Note: In a real app, you'd fetch the existing relations here
    });
    setIsModalOpen(true);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    try {
      if (editingSheet) {
        await api.patch(`/admin/sheets/${editingSheet.id}`, formData);
        toast.success('Sheet updated successfully');
      } else {
        await api.post('/admin/sheets', formData);
        toast.success('Sheet created successfully');
      }
      setIsModalOpen(false);
      fetchSheets();
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Operation failed');
    } finally {
      setIsSubmitting(false);
    }
  };

  const toggleVisibility = async (id: string) => {
    try {
      await api.patch(`/admin/sheets/${id}/toggle`);
      setSheets(sheets.map(s => s.id === id ? { ...s, is_visible: !s.is_visible } : s));
      toast.success('Visibility updated');
    } catch (error) {
      toast.error('Failed to toggle visibility');
    }
  };

  const deleteSheet = async (id: string) => {
    if (!window.confirm('Are you sure you want to delete this sheet?')) return;
    try {
      await api.delete(`/admin/sheets/${id}`);
      setSheets(sheets.filter(s => s.id !== id));
      toast.success('Sheet deleted');
    } catch (error) {
      toast.error('Deletion failed');
    }
  };

  const filteredSheets = sheets.filter(s => 
    s.title.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const getPermissionIcon = (type: string) => {
    switch (type) {
      case 'public': return <Globe className="w-4 h-4" />;
      case 'shift_based': return <Clock className="w-4 h-4" />;
      case 'admin_only': return <Lock className="w-4 h-4" />;
      case 'specific_users': return <Users className="w-4 h-4" />;
      default: return <TableIcon className="w-4 h-4" />;
    }
  };

  return (
    <div className="space-y-8 animate-in fade-in duration-500">
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm relative overflow-hidden">
        <div className="absolute top-0 right-0 w-64 h-64 bg-blue-50/50 rounded-full blur-3xl -mr-32 -mt-32"></div>
        <div className="relative z-10">
          <h2 className="text-3xl font-black tracking-tighter text-slate-900 mb-2">Manage Spreadsheets</h2>
          <p className="text-slate-500 font-medium">Configure Google Sheets access, permissions, and shifts.</p>
        </div>
        <button 
          onClick={openAddModal}
          className="relative z-10 flex items-center justify-center gap-2 px-8 py-4 bg-blue-600 text-white rounded-[1.5rem] font-black shadow-xl shadow-blue-500/20 hover:bg-blue-700 hover:scale-105 active:scale-95 transition-all"
        >
          <Plus className="w-6 h-6" />
          Add New Sheet
        </button>
      </div>

      {/* Main Content */}
      <div className="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">
        {/* Search/Filter Bar */}
        <div className="p-6 border-b border-slate-100 flex items-center justify-between gap-4">
           <div className="relative w-full max-w-md">
              <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
              <input 
                type="text" 
                placeholder="Search sheets..." 
                className="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all font-medium"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
              />
           </div>
           <div className="flex items-center gap-2 text-xs font-black text-slate-400 tracking-widest uppercase">
              <TableIcon className="w-4 h-4" />
              <span>{filteredSheets.length} Total</span>
           </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-slate-50/50">
                <th className="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Sheet / Title</th>
                <th className="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Permission</th>
                <th className="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Constraint</th>
                <th className="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Status</th>
                <th className="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50">
              {isLoading ? (
                Array.from({ length: 5 }).map((_, i) => (
                  <tr key={i} className="animate-pulse">
                    <td colSpan={5} className="px-8 py-6 h-16 bg-white"></td>
                  </tr>
                ))
              ) : filteredSheets.length === 0 ? (
                <tr>
                  <td colSpan={5} className="px-8 py-20 text-center text-slate-400 font-medium italic">No sheets found matching your search.</td>
                </tr>
              ) : (
                filteredSheets.map((sheet) => (
                  <tr key={sheet.id} className="hover:bg-slate-50/50 transition-colors group">
                    <td className="px-8 py-6">
                      <div className="flex items-center gap-4">
                        <div className={`w-12 h-12 flex items-center justify-center rounded-2xl shadow-inner border transition-all ${sheet.is_visible ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-slate-100 text-slate-400 border-slate-200'}`}>
                           <i className={`${sheet.icon} text-xl`}></i>
                        </div>
                        <div>
                          <p className="font-black text-slate-900 group-hover:text-blue-600 transition-colors">{sheet.title}</p>
                        </div>
                      </div>
                    </td>
                    <td className="px-8 py-6">
                      <div className="inline-flex items-center gap-2 px-3 py-1 bg-white border border-slate-200 rounded-full text-[10px] font-black uppercase tracking-wider text-slate-600 shadow-sm">
                        {getPermissionIcon(sheet.permission_type)}
                        {sheet.permission_type.replace('_', ' ')}
                      </div>
                    </td>
                    <td className="px-8 py-6">
                      {sheet.permission_type === 'shift_based' ? (
                        <div className="flex items-center gap-2 text-sm font-bold text-slate-700">
                          <span className="w-2 h-2 rounded-full bg-blue-500"></span>
                          {sheet.shift}
                        </div>
                      ) : sheet.permission_type === 'public' ? (
                        <div className="flex items-center gap-2 text-[10px] font-black text-emerald-600 uppercase tracking-widest">
                           <Globe className="w-3.5 h-3.5" />
                           Everyone
                        </div>
                      ) : (
                        <span className="text-slate-400 text-xs italic">Restricted</span>
                      )}
                    </td>
                    <td className="px-8 py-6">
                       <button 
                        onClick={() => toggleVisibility(sheet.id)}
                        className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tight transition-all ${sheet.is_visible ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400'}`}
                       >
                         {sheet.is_visible ? <Eye className="w-3 h-3" /> : <EyeOff className="w-3 h-3" />}
                         {sheet.is_visible ? 'Visible' : 'Hidden'}
                       </button>
                    </td>
                    <td className="px-8 py-6 text-right">
                       <div className="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                          <Link 
                            to={`/admin/sheets/view/${sheet.slug}`}
                            className="p-2 text-slate-400 hover:text-blue-600 transition-all hover:bg-white rounded-xl shadow-sm border border-transparent hover:border-slate-200"
                            title="View internally"
                          >
                             <Eye className="w-4 h-4" />
                          </Link>
                          <button onClick={() => openEditModal(sheet)} className="p-2 text-slate-400 hover:text-amber-600 transition-all hover:bg-white rounded-xl shadow-sm border border-transparent hover:border-slate-200">
                             <Edit2 className="w-4 h-4" />
                          </button>
                          <button onClick={() => deleteSheet(sheet.id)} className="p-2 text-slate-400 hover:text-red-500 transition-all hover:bg-white rounded-xl shadow-sm border border-transparent hover:border-slate-200">
                             <Trash2 className="w-4 h-4" />
                          </button>
                       </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal Overlay */}
      {isModalOpen && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4">
          <div className="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onClick={() => setIsModalOpen(false)}></div>
          
          <form 
            onSubmit={handleSubmit}
            className="relative w-full max-w-2xl bg-white rounded-[2.5rem] shadow-2xl border border-white overflow-hidden animate-in zoom-in slide-in-from-bottom-8 duration-300"
          >
            {/* Modal Header */}
            <div className="p-8 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
              <div>
                <h3 className="text-2xl font-black text-slate-900 tracking-tight">
                  {editingSheet ? 'Edit Sheet' : 'Create Google Sheet'}
                </h3>
                <p className="text-slate-500 text-sm font-medium">Define metadata and permissions.</p>
              </div>
              <button 
                type="button"
                onClick={() => setIsModalOpen(false)}
                className="w-10 h-10 flex items-center justify-center bg-white border border-slate-200 text-slate-400 rounded-xl hover:text-slate-900 transition-colors"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Modal Body */}
            <div className="p-8 space-y-6 max-h-[70vh] overflow-y-auto">
              {/* Basic Info */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="space-y-1.5">
                  <label className="text-xs font-black text-slate-400 uppercase tracking-widest pl-1">Sheet Title</label>
                  <input 
                    type="text" 
                    required
                    value={formData.title}
                    onChange={(e) => setFormData({...formData, title: e.target.value})}
                    placeholder="e.g. Sales Report 2024"
                    className="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:border-blue-500 transition-all font-bold"
                  />
                </div>
                <div className="space-y-1.5">
                  <label className="text-xs font-black text-slate-400 uppercase tracking-widest pl-1">FontAwesome Icon</label>
                  <input 
                    type="text" 
                    value={formData.icon}
                    onChange={(e) => setFormData({...formData, icon: e.target.value})}
                    placeholder="fas fa-file-excel"
                    className="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:border-blue-500 transition-all font-bold"
                  />
                </div>
              </div>

              <div className="space-y-1.5">
                <label className="text-xs font-black text-slate-400 uppercase tracking-widest pl-1">Iframe Source URL (Google View URL)</label>
                <div className="relative">
                  <Globe className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-300" />
                  <input 
                    type="url" 
                    required
                    value={formData.url}
                    onChange={(e) => setFormData({...formData, url: e.target.value})}
                    placeholder="https://docs.google.com/spreadsheets/d/.../view?rm=minimal"
                    className="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:border-blue-500 transition-all font-bold"
                  />
                </div>
              </div>

              <div className="p-6 bg-blue-50/30 border border-blue-100 rounded-3xl space-y-6">
                <h4 className="text-sm font-black text-blue-600 uppercase tracking-widest">Access Control</h4>
                
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-1.5">
                    <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Permission Type</label>
                    <div className="grid grid-cols-2 gap-2">
                       {(['public', 'shift_based', 'admin_only', 'specific_users'] as const).map(type => (
                         <button
                           key={type}
                           type="button"
                           onClick={() => setFormData({...formData, permission_type: type})}
                           className={`p-3 rounded-xl border text-[10px] font-black uppercase tracking-tighter text-center transition-all ${formData.permission_type === type ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-500/20' : 'bg-white border-slate-200 text-slate-500 hover:border-blue-200'}`}
                         >
                           {type.replace('_', ' ')}
                         </button>
                       ))}
                    </div>
                  </div>

                  {formData.permission_type === 'shift_based' && (
                    <div className="space-y-1.5 animate-in slide-in-from-right-4 duration-300">
                      <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Assign to Shift</label>
                      <select 
                        required
                        value={formData.shift}
                        onChange={(e) => setFormData({...formData, shift: e.target.value})}
                        className="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl outline-none focus:border-blue-500 transition-all font-bold appearance-none cursor-pointer shadow-sm"
                      >
                        <option value="">Select a shift...</option>
                        {shifts.map(s => <option key={s} value={s}>{s}</option>)}
                      </select>
                    </div>
                  )}

                  {formData.permission_type === 'specific_users' && (
                    <div className="space-y-3 animate-in slide-in-from-right-4 duration-300">
                      <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1 flex items-center gap-2">
                        <Users className="w-3.5 h-3.5 text-blue-500" />
                        Select Authorized Workers
                      </label>
                      <div className="max-h-40 overflow-y-auto bg-white border border-slate-200 rounded-2xl p-2 space-y-1 custom-scrollbar shadow-inner">
                        {workers.length === 0 ? (
                          <p className="text-[10px] text-slate-400 p-4 text-center italic">No workers found.</p>
                        ) : (
                          workers.map(w => (
                            <button
                              key={w.id}
                              type="button"
                              onClick={() => {
                                const ids = formData.user_ids.includes(w.id) 
                                  ? formData.user_ids.filter(id => id !== w.id)
                                  : [...formData.user_ids, w.id];
                                setFormData({...formData, user_ids: ids});
                              }}
                              className={`w-full flex items-center justify-between p-2.5 rounded-xl transition-all text-left ${formData.user_ids.includes(w.id) ? 'bg-blue-50 text-blue-700' : 'hover:bg-slate-50 text-slate-600'}`}
                            >
                              <div className="flex items-center gap-2">
                                <div className={`w-6 h-6 rounded-lg flex items-center justify-center text-[10px] font-bold ${formData.user_ids.includes(w.id) ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-400'}`}>
                                  {w.name.charAt(0)}
                                </div>
                                <span className="text-xs font-bold truncate">{w.name}</span>
                              </div>
                              {formData.user_ids.includes(w.id) && <Check className="w-3.5 h-3.5" />}
                            </button>
                          ))
                        )}
                      </div>
                      <p className="text-[10px] font-bold text-slate-400 pl-1 mt-1 italic">
                        {formData.user_ids.length} users selected. They will see this sheet in their portal.
                      </p>
                    </div>
                  )}
                </div>
              </div>
            </div>

            {/* Modal Footer */}
            <div className="p-8 bg-slate-50/50 border-t border-slate-100 flex items-center justify-end gap-4">
              <button 
                type="button" 
                onClick={() => setIsModalOpen(false)}
                className="px-6 py-4 text-sm font-black text-slate-400 hover:text-slate-900 transition-colors uppercase tracking-widest"
              >
                Cancel
              </button>
              <button 
                type="submit" 
                disabled={isSubmitting}
                className="flex items-center gap-2 px-10 py-4 bg-slate-900 text-white rounded-2xl font-black shadow-xl shadow-slate-900/20 hover:bg-slate-800 active:scale-95 transition-all disabled:opacity-50"
              >
                {isSubmitting ? <Loader2 className="w-5 h-5 animate-spin" /> : <Check className="w-5 h-5" />}
                {editingSheet ? 'Save Changes' : 'Publish Sheet'}
              </button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
}
