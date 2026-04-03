import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { api } from '../../lib/api';
import { KeyRound, Mail, ArrowLeft, Loader2, CheckCircle2, AlertCircle, ShieldQuestion } from 'lucide-react';

export function ForgotPassword() {
  const [step, setStep] = useState<1 | 2 | 3>(1);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const navigate = useNavigate();

  const handleCheckEmail = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setLoading(true);
    
    try {
      await api.post('/auth/check-email', { email });
      setStep(2);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Email not found in our records.');
    } finally {
      setLoading(false);
    }
  };

  const handleResetPassword = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);

    if (password !== confirmPassword) {
      setError('Passwords do not match');
      return;
    }

    setLoading(true);
    try {
      await api.post('/auth/reset-password-direct', { email, password });
      setStep(3);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to reset password. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-slate-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-[url('https://images.unsplash.com/photo-1451187580459-43490279c0fa?q=80&w=2072&auto=format&fit=crop')] bg-cover bg-center">
      <div className="absolute inset-0 bg-slate-900/80 backdrop-blur-sm"></div>
      
      <div className="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
        <div className="flex justify-center flex-col items-center">
          <div className="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/50 mb-4 animate-bounce">
            <ShieldQuestion className="w-8 h-8 text-white" />
          </div>
          <h2 className="text-center text-3xl font-extrabold text-white">
            {step === 3 ? 'Password Reset' : 'Account Recovery'}
          </h2>
          <p className="mt-2 text-center text-sm text-slate-300">
            {step === 1 && 'Enter your email to search for your account.'}
            {step === 2 && 'Enter a new secure password.'}
            {step === 3 && 'Your password has been successfully updated.'}
          </p>
        </div>
      </div>

      <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10">
        <div className="bg-white/10 backdrop-blur-md py-8 px-4 shadow-2xl sm:rounded-2xl sm:px-10 border border-white/20">
          
          {error && (
            <div className="mb-4 bg-red-500/20 border border-red-500/50 p-3 rounded-lg flex items-center gap-3 animate-in fade-in zoom-in duration-300">
              <div className="w-2 h-2 rounded-full bg-red-500 animate-pulse shrink-0"></div>
              <p className="text-red-200 text-sm font-medium">{error}</p>
            </div>
          )}

          {step === 3 ? (
            <div className="flex flex-col items-center justify-center py-4 text-center animate-in zoom-in duration-500">
              <div className="mb-6 flex h-20 w-20 items-center justify-center rounded-3xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-[0_0_30px_rgba(16,185,129,0.2)]">
                <CheckCircle2 className="h-10 w-10" />
              </div>
              <h3 className="text-xl font-bold text-white mb-2">Success!</h3>
              <p className="text-slate-300 mb-8 max-w-[250px]">You can now sign in to your dashboard with your new password.</p>
              
              <button
                onClick={() => navigate('/login')}
                className="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-black text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-slate-900 transition-all shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50"
              >
                Return to Login
              </button>
            </div>
          ) : (
            <form className="space-y-6" onSubmit={step === 1 ? handleCheckEmail : handleResetPassword}>
              
              {/* STEP 1: Email Input */}
              {step === 1 && (
                <div className="animate-in fade-in slide-in-from-right-4 duration-500">
                  <label className="block text-sm font-medium text-slate-200 mb-1.5">
                    Email address
                  </label>
                  <div className="relative rounded-md shadow-sm">
                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <Mail className="h-5 w-5 text-slate-400" />
                    </div>
                    <input
                      type="email"
                      required
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      className="block w-full pl-10 bg-slate-900/50 border border-slate-600/50 rounded-xl py-3.5 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                      placeholder="admin@example.com"
                    />
                  </div>
                </div>
              )}

              {/* STEP 2: Password Inputs */}
              {step === 2 && (
                <div className="space-y-5 animate-in fade-in slide-in-from-right-4 duration-500">
                  <div className="p-3 bg-blue-500/10 border border-blue-500/20 rounded-xl flex items-center gap-3 mb-2 text-sm text-blue-200">
                    <CheckCircle2 className="w-4 h-4 text-blue-400 shrink-0" />
                    <span>Account found: <strong className="text-white">{email}</strong></span>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-slate-200 mb-1.5">
                      New Password
                    </label>
                    <div className="relative rounded-md shadow-sm">
                      <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <KeyRound className="h-5 w-5 text-slate-400" />
                      </div>
                      <input
                        type="password"
                        required
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        className="block w-full pl-10 bg-slate-900/50 border border-slate-600/50 rounded-xl py-3.5 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="••••••••"
                      />
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-slate-200 mb-1.5">
                      Confirm New Password
                    </label>
                    <div className="relative rounded-md shadow-sm">
                      <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <KeyRound className="h-5 w-5 text-slate-400" />
                      </div>
                      <input
                        type="password"
                        required
                        value={confirmPassword}
                        onChange={(e) => setConfirmPassword(e.target.value)}
                        className="block w-full pl-10 bg-slate-900/50 border border-slate-600/50 rounded-xl py-3.5 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="••••••••"
                      />
                    </div>
                  </div>
                </div>
              )}

              <div className="pt-2">
                <button
                  type="submit"
                  disabled={loading}
                  className="w-full flex justify-center items-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-black text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-slate-900 transition-all shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 disabled:opacity-50 disabled:cursor-not-allowed group"
                >
                  {loading ? (
                    <Loader2 className="h-5 w-5 animate-spin" />
                  ) : (
                    <span>{step === 1 ? 'Search Account' : 'Update Password'}</span>
                  )}
                  
                  {!loading && (
                    <span className="absolute inset-y-0 right-0 pr-4 flex items-center opacity-0 group-hover:opacity-100 transition duration-300 transform translate-x-3 group-hover:translate-x-0">
                      &rarr;
                    </span>
                  )}
                </button>
              </div>

              <div className="text-center pt-2">
                <Link
                  to="/login"
                  className="inline-flex items-center text-sm font-medium text-slate-400 hover:text-white transition-colors group"
                >
                  <ArrowLeft className="mr-2 h-4 w-4 transform group-hover:-translate-x-1 transition-transform" />
                  Back to login
                </Link>
              </div>
            </form>
          )}

        </div>
      </div>
    </div>
  );
}
