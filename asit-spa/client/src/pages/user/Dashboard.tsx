import { useState, useEffect } from 'react';
import { api } from '../../lib/api';
import { 
  ListTodo, 
  TrendingUp,
  Clock,
  History,
  Activity
} from 'lucide-react';
import { format } from 'date-fns';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Filler,
  Legend,
} from 'chart.js';
import { Line } from 'react-chartjs-2';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Filler,
  Legend
);

interface UserStats {
  today: number;
  yesterday: number;
  month: number;
  lifetime: number;
  chart: {
    labels: string[];
    data: number[];
  };
}

export function UserDashboard() {
  const [stats, setStats] = useState<UserStats | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchStats = async () => {
      try {
        const { data } = await api.get('/user/dashboard/stats');
        setStats(data);
      } catch (error) {
        console.error('Failed to fetch user stats', error);
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

  const chartData = {
    labels: stats.chart.labels,
    datasets: [
      {
        fill: true,
        label: 'Tokens Processed',
        data: stats.chart.data,
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4,
      },
    ],
  };

  const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: false,
      },
      tooltip: {
        mode: 'index' as const,
        intersect: false,
      },
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          stepSize: 1
        }
      }
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-2xl font-bold tracking-tight text-slate-800">My Overview</h2>
          <p className="text-slate-500">Track your performance and token processing history.</p>
        </div>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
          <div className="flex items-center justify-between pb-4">
            <h3 className="text-sm font-medium text-slate-500">Today's Shift</h3>
            <div className="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center">
              <ListTodo className="w-5 h-5 text-blue-600" />
            </div>
          </div>
          <div className="text-3xl font-bold text-slate-800">{stats.today}</div>
          <div className="mt-2 flex items-center gap-2">
            <span className={`text-xs font-medium flex items-center ${stats.today >= stats.yesterday ? 'text-green-600' : 'text-slate-500'}`}>
              <TrendingUp className="w-3 h-3 mr-1" />
              {stats.yesterday} yesterday
            </span>
          </div>
        </div>

        <div className="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
          <div className="flex items-center justify-between pb-4">
            <h3 className="text-sm font-medium text-slate-500">Current Month</h3>
            <div className="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center">
              <Activity className="w-5 h-5 text-purple-600" />
            </div>
          </div>
          <div className="text-3xl font-bold text-slate-800">{stats.month}</div>
          <div className="mt-2 text-xs text-slate-400 font-medium flex items-center">
            <Clock className="w-3 h-3 mr-1" />
            Since {format(new Date(new Date().getFullYear(), new Date().getMonth(), 1), 'MMM 1')}
          </div>
        </div>

        <div className="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 lg:col-span-2">
          <div className="flex items-center justify-between pb-4">
            <h3 className="text-sm font-medium text-slate-500">Lifetime Total</h3>
            <div className="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center">
              <History className="w-5 h-5 text-indigo-600" />
            </div>
          </div>
          <div className="text-3xl font-bold text-slate-800">{stats.lifetime}</div>
          <div className="mt-2 text-xs text-slate-400 font-medium flex items-center">
            All-time tokens processed
          </div>
        </div>
      </div>

      {/* Performance Chart */}
      <div className="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <h3 className="text-base font-semibold text-slate-800 mb-6">7-Day Performance</h3>
        <div className="h-[300px] w-full">
          <Line data={chartData} options={chartOptions} />
        </div>
      </div>
    </div>
  );
}
