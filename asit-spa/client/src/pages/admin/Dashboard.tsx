import { useState, useEffect } from 'react';
import { api } from '../../lib/api';
import { 
  Users, 
  ListTodo, 
  Activity,
  ArrowUpRight,
  TrendingUp,
  Clock,
  MonitorPlay
} from 'lucide-react';
import { format } from 'date-fns';

interface DashboardStats {
  workers: number;
  tokens: {
    today: number;
    yesterday: number;
    month: number;
    lifetime: number;
  };
  live_devices: number;
  recent_tokens: any[];
}

export function AdminDashboard() {
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchStats = async () => {
      try {
        const { data } = await api.get('/admin/dashboard/stats');
        setStats(data);
      } catch (error) {
        console.error('Failed to fetch dashboard stats', error);
      } finally {
        setIsLoading(false);
      }
    };
    fetchStats();
  }, []);

  if (isLoading) {
    return <div className="flex justify-center p-12"><div className="w-8 h-8 rounded-full border-4 border-blue-500 border-t-transparent animate-spin"></div></div>;
  }

  if (!stats) return null;

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-2xl font-bold tracking-tight text-slate-800">Admin Overview</h2>
          <p className="text-slate-500">Real-time statistics for today, {format(new Date(), 'MMMM d, yyyy')}</p>
        </div>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
          <div className="flex items-center justify-between pb-4">
            <h3 className="text-sm font-medium text-slate-500">Active Workers</h3>
            <div className="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center">
              <Users className="w-5 h-5 text-blue-600" />
            </div>
          </div>
          <div className="text-3xl font-bold text-slate-800">{stats.workers}</div>
          <div className="mt-2 text-xs text-green-600 flex items-center font-medium">
            <ArrowUpRight className="w-3 h-3 mr-1" />
            Active today
          </div>
        </div>

        <div className="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
          <div className="flex items-center justify-between pb-4">
            <h3 className="text-sm font-medium text-slate-500">Tokens Today</h3>
            <div className="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center">
              <ListTodo className="w-5 h-5 text-indigo-600" />
            </div>
          </div>
          <div className="text-3xl font-bold text-slate-800">{stats.tokens.today}</div>
          <div className="mt-2 flex items-center gap-2">
            <span className={`text-xs font-medium flex items-center ${stats.tokens.today >= stats.tokens.yesterday ? 'text-green-600' : 'text-red-500'}`}>
              <TrendingUp className="w-3 h-3 mr-1" />
              {stats.tokens.yesterday} yesterday
            </span>
          </div>
        </div>

        <div className="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
          <div className="flex items-center justify-between pb-4">
            <h3 className="text-sm font-medium text-slate-500">Monthly Tokens</h3>
            <div className="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center">
              <Activity className="w-5 h-5 text-purple-600" />
            </div>
          </div>
          <div className="text-3xl font-bold text-slate-800">{stats.tokens.month}</div>
          <div className="mt-2 text-xs text-slate-400 font-medium flex items-center">
            <Clock className="w-3 h-3 mr-1" />
            Current month cycle
          </div>
        </div>

        <div className="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 shadow-sm border border-slate-800 text-white">
          <div className="flex items-center justify-between pb-4">
            <h3 className="text-sm font-medium text-slate-300">Live Devices</h3>
            <div className="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center">
              <MonitorPlay className="w-5 h-5 text-green-400" />
            </div>
          </div>
          <div className="text-3xl font-bold text-white">{stats.live_devices}</div>
          <div className="mt-2 text-xs text-green-400 font-medium flex items-center">
            <div className="w-2 h-2 rounded-full bg-green-500 mr-2 animate-pulse"></div>
            Connected agents
          </div>
        </div>
      </div>

      {/* Recent Activity */}
      <div className="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div className="px-6 py-5 border-b border-slate-100">
          <h3 className="text-base font-semibold text-slate-800">Recent Token Activity</h3>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left">
            <thead className="bg-slate-50 text-slate-500 uppercase text-xs font-semibold">
              <tr>
                <th className="px-6 py-4">Worker</th>
                <th className="px-6 py-4">Token</th>
                <th className="px-6 py-4">Status</th>
                <th className="px-6 py-4 text-right">Time</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {stats.recent_tokens.map((token, i) => (
                <tr key={i} className="hover:bg-slate-50 transition-colors">
                  <td className="px-6 py-4 font-medium text-slate-800">{token.user_name}</td>
                  <td className="px-6 py-4">
                    <span className="font-mono text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded border border-slate-200">
                      {token.live_token}
                    </span>
                  </td>
                  <td className="px-6 py-4">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                      token.status === 'valid' ? 'bg-green-100 text-green-800' : 
                      token.status === 'duplicate' ? 'bg-yellow-100 text-yellow-800' : 
                      'bg-slate-100 text-slate-800'
                    }`}>
                      {token.status?.toUpperCase() || 'NEW'}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-right text-slate-500">
                    {token.insert_time ? (() => {
                      const d = new Date(token.insert_time);
                      return isNaN(d.getTime()) ? 'Invalid date' : format(d, 'hh:mm a');
                    })() : 'N/A'}
                  </td>
                </tr>
              ))}
              {stats.recent_tokens.length === 0 && (
                <tr>
                  <td colSpan={4} className="px-6 py-8 text-center text-slate-500">
                    No recent tokens found for today's shift.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
