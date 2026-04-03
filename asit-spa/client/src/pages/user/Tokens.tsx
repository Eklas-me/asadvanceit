import { useState, useEffect } from 'react';
import { api } from '../../lib/api';
import { 
  PlusCircle, 
  Search,
  CheckCircle2, 
  XCircle,
  Copy,
  Clock
} from 'lucide-react';
import { format } from 'date-fns';

interface TokenItem {
  id: string;
  live_token: string;
  insert_time: string;
  status: string;
  admin?: {
    name: string;
  };
}

export function UserTokens() {
  const [tokens, setTokens] = useState<TokenItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [newToken, setNewToken] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitMessage, setSubmitMessage] = useState({ text: '', type: '' });
  
  const fetchTokens = async () => {
    try {
      setIsLoading(true);
      const { data } = await api.get(`/user/tokens`);
      setTokens(data);
    } catch (error) {
      console.error('Failed to fetch tokens', error);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchTokens();
  }, []);

  const handleSubmitToken = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newToken.trim()) return;

    setIsSubmitting(true);
    setSubmitMessage({ text: '', type: '' });

    try {
      const { data } = await api.post('/user/tokens', { live_token: newToken.trim() });
      setSubmitMessage({ text: 'Token processed successfully!', type: 'success' });
      setNewToken('');
      // Optimistically add to UI if valid or duplicate
      if (data.status === 'valid') {
        const fakeId = Math.random().toString(36).substr(2, 9);
        setTokens([{
          id: fakeId,
          live_token: newToken.trim(),
          insert_time: new Date().toISOString(),
          status: 'valid'
        }, ...tokens]);
      } else if (data.status === 'duplicate') {
         setSubmitMessage({ text: 'Warning: This token is a duplicate within the 72 hour window.', type: 'warning' });
      }
    } catch (error: any) {
      setSubmitMessage({ 
        text: error.response?.data?.message || 'Failed to submit token. Please try again.', 
        type: 'error' 
      });
    } finally {
      setIsSubmitting(false);
      // Refresh strictly from backend state after a moment
      setTimeout(fetchTokens, 1500); 
    }
  };

  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
  };

  const filteredTokens = tokens.filter(t => 
    t.live_token.includes(searchTerm)
  );

  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-2xl font-bold tracking-tight text-slate-800">My Tokens</h2>
        <p className="text-slate-500">Submit new live tokens and view your recent history.</p>
      </div>

      {/* Token Submission Form */}
      <div className="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden relative">
        <div className="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
        <div className="p-6 md:p-8">
          <form onSubmit={handleSubmitToken} className="max-w-2xl">
            <label htmlFor="token-input" className="block text-sm font-medium text-slate-700 mb-2">
              Submit Live Token
            </label>
            <div className="flex flex-col sm:flex-row gap-3">
              <div className="relative flex-grow">
                <input
                  id="token-input"
                  type="text"
                  required
                  value={newToken}
                  onChange={(e) => setNewToken(e.target.value)}
                  placeholder="Paste token string here..."
                  className="block w-full pl-4 pr-10 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl outline-none text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  disabled={isSubmitting}
                />
              </div>
              <button
                type="submit"
                disabled={isSubmitting || !newToken.trim()}
                className="inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-medium rounded-xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap"
              >
                {isSubmitting ? (
                  <div className="w-5 h-5 rounded-full border-2 border-white border-t-transparent animate-spin"></div>
                ) : (
                  <>
                    <PlusCircle className="w-5 h-5 mr-2" />
                    Submit Token
                  </>
                )}
              </button>
            </div>
            {submitMessage.text && (
               <div className={`mt-3 text-sm font-medium ${
                 submitMessage.type === 'success' ? 'text-green-600' : 
                 submitMessage.type === 'warning' ? 'text-amber-600' : 'text-red-500'
               }`}>
                 {submitMessage.text}
               </div>
            )}
            <p className="mt-2 text-xs text-slate-500 flex items-center">
              <Clock className="w-3.5 h-3.5 mr-1" />
              Tokens submitted are automatically bound to your current active shift parameters.
            </p>
          </form>
        </div>
      </div>

      {/* History Area */}
      <div className="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div className="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <h3 className="font-semibold text-slate-800">Recent Submissions</h3>
          <div className="relative w-64 border border-slate-200 rounded-lg bg-white shadow-sm">
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <Search className="h-4 w-4 text-slate-400" />
            </div>
            <input
              type="text"
              className="block w-full pl-10 pr-3 py-2 text-sm outline-none text-slate-800 placeholder-slate-400 bg-transparent"
              placeholder="Search history..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left">
            <thead className="bg-slate-50 text-slate-500 uppercase text-xs font-semibold">
              <tr>
                <th className="px-6 py-4">Token String</th>
                <th className="px-6 py-4">Verification</th>
                <th className="px-6 py-4">Timestamp</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {isLoading ? (
                <tr>
                  <td colSpan={3} className="px-6 py-12 text-center text-slate-500">
                    <div className="w-6 h-6 rounded-full border-2 border-blue-500 border-t-transparent animate-spin mx-auto"></div>
                  </td>
                </tr>
              ) : filteredTokens.length === 0 ? (
                <tr>
                  <td colSpan={3} className="px-6 py-8 text-center text-slate-500">
                    No tokens found in your recent history.
                  </td>
                </tr>
              ) : (
                filteredTokens.map((token) => (
                  <tr key={token.id} className="hover:bg-slate-50/50 transition-colors group">
                    <td className="px-6 py-4">
                      <div className="flex items-center gap-2">
                        <span className="font-mono text-sm bg-slate-100/80 text-slate-800 px-3 py-1.5 rounded-lg border border-slate-200 break-all select-all">
                          {token.live_token}
                        </span>
                        <button 
                          onClick={() => copyToClipboard(token.live_token)}
                          className="text-slate-400 hover:text-blue-600 opacity-0 group-hover:opacity-100 transition duration-200 p-1 bg-white border border-slate-200 rounded shadow-sm"
                          title="Copy to clipboard"
                        >
                          <Copy className="h-3.5 w-3.5" />
                        </button>
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border ${
                          token.status === 'valid' 
                            ? 'bg-green-50 text-green-700 border-green-200' 
                            : token.status === 'duplicate' 
                            ? 'bg-yellow-50 text-yellow-700 border-yellow-200'
                            : 'bg-red-50 text-red-700 border-red-200'
                      }`}>
                        {token.status === 'valid' ? <CheckCircle2 className="w-3.5 h-3.5" /> : <XCircle className="w-3.5 h-3.5" />}
                        {token.status?.toUpperCase()}
                      </span>
                      {token.admin && (
                        <div className="text-[10px] text-slate-400 mt-1 pl-1">
                          Audited by Admin
                        </div>
                      )}
                    </td>
                    <td className="px-6 py-4 text-slate-500">
                      {format(new Date(token.insert_time), 'MMM d, yyyy • hh:mm a')}
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
