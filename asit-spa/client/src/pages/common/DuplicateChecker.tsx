import { useState } from 'react';
import { api } from '../../lib/api';
import { Search, AlertCircle, CheckCircle2, Copy } from 'lucide-react';

export function DuplicateChecker() {
  const [tokens, setTokens] = useState<string>('');
  const [results, setResults] = useState<any[] | null>(null);
  const [isChecking, setIsChecking] = useState(false);
  const [error, setError] = useState('');

  const handleCheck = async () => {
    if (!tokens.trim()) {
      setError('Please enter at least one token to check.');
      return;
    }
    
    setError('');
    setIsChecking(true);
    
    try {
      const tokenList = tokens.split(/[\n,]+/).map(t => t.trim()).filter(Boolean);
      const { data } = await api.post('/common/duplicate-checker', { tokens: tokenList });
      setResults(data.data);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to check tokens');
    } finally {
      setIsChecking(false);
    }
  };

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      <div>
        <h2 className="text-2xl font-bold tracking-tight text-slate-800">Duplicate Checker</h2>
        <p className="text-slate-500">Bulk verify live tokens against the 72-hour duplicate window before submission.</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
          <label className="block text-sm font-semibold text-slate-800 mb-3">
            Tokens to Check (one per line or comma-separated)
          </label>
          <textarea 
            value={tokens}
            onChange={(e) => setTokens(e.target.value)}
            className="w-full h-64 p-4 text-sm font-mono bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"
            placeholder="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...&#10;eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
          ></textarea>
          
          <div className="mt-4 flex items-center justify-between">
            <span className="text-xs text-slate-500 font-medium">
              {tokens.split(/[\n,]+/).filter(Boolean).length} tokens detected
            </span>
            <button 
              onClick={handleCheck}
              disabled={isChecking || tokens.trim() === ''}
              className="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition shadow-sm disabled:opacity-50 flex items-center gap-2"
            >
              {isChecking ? (
                <div className="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin"></div>
              ) : (
                <Search className="w-4 h-4" />
              )}
              Run Verification
            </button>
          </div>
          {error && <p className="mt-3 text-sm text-red-500 font-medium">{error}</p>}
        </div>

        <div className="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex flex-col h-[400px]">
          <h3 className="text-sm font-semibold text-slate-800 mb-4 pb-4 border-b border-slate-100">Verification Results</h3>
          <div className="flex-1 overflow-y-auto pr-2">
            {!results ? (
              <div className="h-full flex flex-col items-center justify-center text-slate-400">
                <Search className="w-12 h-12 mb-3 opacity-20" />
                <p>Paste tokens and click verify to see results.</p>
              </div>
            ) : results.length === 0 ? (
              <div className="h-full flex items-center justify-center text-slate-500">
                No results.
              </div>
            ) : (
              <ul className="space-y-3">
                {results.map((r, i) => (
                  <li key={i} className={`p-4 rounded-xl border ${r.isDuplicate ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200'}`}>
                    <div className="flex items-start gap-3">
                      {r.isDuplicate ? (
                        <AlertCircle className="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
                      ) : (
                        <CheckCircle2 className="w-5 h-5 text-green-500 shrink-0 mt-0.5" />
                      )}
                      <div className="min-w-0">
                        <div className="flex items-center gap-2 mb-1">
                          <span className={`text-xs font-bold uppercase tracking-wider ${r.isDuplicate ? 'text-red-700' : 'text-green-700'}`}>
                            {r.isDuplicate ? 'Duplicate Found' : 'Safe to Submit'}
                          </span>
                        </div>
                        <p className="text-sm font-mono text-slate-600 truncate bg-white/50 px-2 py-1 rounded inline-block w-full max-w-full">
                          {r.token}
                        </p>
                        {r.isDuplicate && r.lastSubmitted && (
                          <p className="text-xs text-red-600 mt-2 font-medium">
                            Previously recorded on: {new Date(r.lastSubmitted).toLocaleString()}
                          </p>
                        )}
                      </div>
                      <button 
                        onClick={() => navigator.clipboard.writeText(r.token)}
                        className="ml-auto p-1.5 bg-white rounded-lg shadow-sm border border-slate-200 text-slate-400 hover:text-blue-600 transition"
                      >
                        <Copy className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </li>
                ))}
              </ul>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
