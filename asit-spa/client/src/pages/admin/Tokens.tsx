import { useState, useEffect } from 'react';
import { api } from '../../lib/api';
import { 
  Search, 
  Trash2, 
  CheckCircle2, 
  XCircle,
  Copy
} from 'lucide-react';
import { format } from 'date-fns';

interface TokenItem {
  id: string;
  live_token: string;
  insert_time: string;
  worker_id: string;
  admin_id: string | null;
  status: string;
  users: {
    name: string;
    shift: string;
    role: string;
  };
  admin?: {
    name: string;
  };
}

export function AdminTokens() {
  const [tokens, setTokens] = useState<TokenItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  
  const fetchTokens = async (p = 1) => {
    try {
      setIsLoading(true);
      const { data } = await api.get(`/admin/tokens?page=${p}`);
      setTokens(data.data);
      setTotalPages(data.meta.last_page);
    } catch (error) {
      console.error('Failed to fetch tokens', error);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchTokens(page);
  }, [page]);

  const handleStatusToggle = async (id: string, currentStatus: string) => {
    try {
      const newStatus = currentStatus === 'valid' ? 'invalid' : 'valid';
      await api.patch(`/admin/tokens/${id}/status`, { status: newStatus });
      setTokens(tokens.map(t => t.id === id ? { ...t, status: newStatus } : t));
    } catch (error) {
      console.error('Failed to update status', error);
    }
  };

  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
  };

  const filteredTokens = tokens.filter(t => 
    t.live_token.includes(searchTerm) || 
    t.users?.name.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-2xl font-bold tracking-tight text-slate-800">Token Management</h2>
          <p className="text-slate-500">Monitor and manage all processed tokens submitted by workers.</p>
        </div>
      </div>

      {/* Filters and Search */}
      <div className="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex items-center justify-between">
        <div className="relative w-full max-w-sm border border-slate-200 rounded-lg bg-slate-50">
          <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <Search className="h-4 w-4 text-slate-400" />
          </div>
          <input
            type="text"
            className="block w-full pl-10 bg-transparent py-2.5 text-sm outline-none text-slate-800 placeholder-slate-400"
            placeholder="Search token string or worker name..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>
      </div>

      {/* Tokens Table */}
      <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left">
            <thead className="bg-slate-50 text-slate-500 uppercase text-xs font-semibold">
              <tr>
                <th className="px-6 py-4">Token String</th>
                <th className="px-6 py-4">Worker details</th>
                <th className="px-6 py-4">Verification</th>
                <th className="px-6 py-4">Timestamp</th>
                <th className="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {isLoading ? (
                <tr>
                  <td colSpan={5} className="px-6 py-12 text-center text-slate-500">
                    <div className="w-6 h-6 rounded-full border-2 border-indigo-500 border-t-transparent animate-spin mx-auto"></div>
                  </td>
                </tr>
              ) : filteredTokens.length === 0 ? (
                <tr>
                  <td colSpan={5} className="px-6 py-8 text-center text-slate-500">
                    No tokens found matching your criteria.
                  </td>
                </tr>
              ) : (
                filteredTokens.map((token) => (
                  <tr key={token.id} className="hover:bg-slate-50/50 transition-colors">
                    <td className="px-6 py-4">
                      <div className="flex items-center gap-2 group">
                        <span className="font-mono text-sm bg-slate-100 text-slate-800 px-3 py-1.5 rounded-lg border border-slate-200 break-all">
                          {token.live_token}
                        </span>
                        <button 
                          onClick={() => copyToClipboard(token.live_token)}
                          className="text-slate-400 hover:text-indigo-600 opacity-0 group-hover:opacity-100 transition duration-200 p-1 bg-white border border-slate-200 rounded"
                          title="Copy to clipboard"
                        >
                          <Copy className="h-3 w-3" />
                        </button>
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <div className="flex flex-col gap-0.5">
                        <span className="font-medium text-slate-700">{token.users?.name || 'Unknown Worker'}</span>
                        <span className="text-xs text-slate-500 capitalize">{token.users?.shift || 'No shift'}</span>
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <button 
                        onClick={() => handleStatusToggle(token.id, token.status)}
                        className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium cursor-pointer transition ${
                          token.status === 'valid' 
                            ? 'bg-green-50 text-green-700 hover:bg-green-100 border border-green-200' 
                            : token.status === 'duplicate' 
                            ? 'bg-yellow-50 text-yellow-700 border border-yellow-200 cursor-not-allowed hover:bg-yellow-50'
                            : 'bg-red-50 text-red-700 hover:bg-red-100 border border-red-200'
                        }`}
                        title={token.status === 'duplicate' ? 'Auto-detected duplicate cannot be marked valid' : 'Click to toggle status'}
                        disabled={token.status === 'duplicate'}
                      >
                        {token.status === 'valid' ? <CheckCircle2 className="w-3.5 h-3.5" /> : <XCircle className="w-3.5 h-3.5" />}
                        {token.status?.toUpperCase()}
                      </button>
                      {token.admin_id && (
                        <div className="text-[10px] text-slate-400 mt-1">
                          Verified by {token.admin?.name || 'Admin'}
                        </div>
                      )}
                    </td>
                    <td className="px-6 py-4 text-slate-500">
                      {format(new Date(token.insert_time), 'MMM d, hh:mm a')}
                    </td>
                    <td className="px-6 py-4 text-right">
                      <button className="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete record">
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
        
        {/* Pagination */}
        {totalPages > 1 && (
          <div className="flex items-center justify-between px-6 py-3 border-t border-slate-100 bg-slate-50">
            <div className="text-sm text-slate-500">
              Page <span className="font-medium">{page}</span> of <span className="font-medium">{totalPages}</span>
            </div>
            <div className="flex gap-2">
              <button 
                onClick={() => setPage(Math.max(1, page - 1))}
                disabled={page === 1}
                className="px-3 py-1 bg-white border border-slate-200 rounded text-sm disabled:opacity-50 hover:bg-slate-50"
              >
                Previous
              </button>
              <button 
                onClick={() => setPage(Math.min(totalPages, page + 1))}
                disabled={page === totalPages}
                className="px-3 py-1 bg-white border border-slate-200 rounded text-sm disabled:opacity-50 hover:bg-slate-50"
              >
                Next
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
