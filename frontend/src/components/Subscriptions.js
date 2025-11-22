import React, { useState, useEffect } from 'react';
import api from '../services/api';

function Subscriptions() {
  const [subscriptions, setSubscriptions] = useState([]);
  const [plans, setPlans] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [actionLoading, setActionLoading] = useState(null);

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      const [subsResponse, plansResponse] = await Promise.all([
        api.get('/subscriptions'),
        api.get('/plans')
      ]);
      setSubscriptions(subsResponse.data);
      setPlans(plansResponse.data);
    } catch (err) {
      setError('Failed to load subscriptions');
    } finally {
      setLoading(false);
    }
  };

  const handleCancel = async (subscriptionId) => {
    if (!window.confirm('Are you sure you want to cancel this subscription?')) return;

    setActionLoading(subscriptionId);
    try {
      await api.post(`/subscriptions/${subscriptionId}/cancel`);
      setSubscriptions(subscriptions.map(sub => 
        sub.id === subscriptionId ? { ...sub, status: 'canceled' } : sub
      ));
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to cancel subscription');
    } finally {
      setActionLoading(null);
    }
  };

  const handleResume = async (subscriptionId) => {
    setActionLoading(subscriptionId);
    try {
      await api.post(`/subscriptions/${subscriptionId}/resume`);
      setSubscriptions(subscriptions.map(sub => 
        sub.id === subscriptionId ? { ...sub, status: 'active' } : sub
      ));
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to resume subscription');
    } finally {
      setActionLoading(null);
    }
  };

  const handleChangePlan = async (subscriptionId, newPlanId) => {
    setActionLoading(subscriptionId);
    try {
      await api.post(`/subscriptions/${subscriptionId}/change-plan`, {
        plan_id: newPlanId
      });
      await fetchData(); // Reload all subscriptions
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to change plan');
    } finally {
      setActionLoading(null);
    }
  };

  if (loading) return <div className="container"><p>Loading...</p></div>;

  return (
    <div className="container">
      <h1>My Subscriptions</h1>
      {error && <div className="alert alert-error">{error}</div>}

      {subscriptions.length === 0 ? (
        <div className="card" style={{ textAlign: 'center', padding: '40px' }}>
          <p>You don't have any active subscriptions.</p>
          <a href="/plans" className="btn btn-primary" style={{ textDecoration: 'none' }}>
            View Available Plans
          </a>
        </div>
      ) : (
        <div style={{ marginTop: '20px' }}>
          {subscriptions.map(subscription => {
            const plan = plans.find(p => p.id === subscription.plan_id);
            const isActive = subscription.status === 'active';
            const isCanceled = subscription.status === 'canceled';

            return (
              <div key={subscription.id} className="card" style={{ marginBottom: '20px' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'start' }}>
                  <div>
                    <h3>{plan?.name || 'Unknown Plan'}</h3>
                    <p><strong>Status:</strong> <span style={{ 
                      color: isActive ? 'green' : isCanceled ? 'red' : 'orange',
                      textTransform: 'capitalize'
                    }}>{subscription.status}</span></p>
                    <p><strong>Price:</strong> ${plan?.price}/{plan?.billing_interval}</p>
                    <p><strong>Started:</strong> {new Date(subscription.start_date).toLocaleDateString()}</p>
                    {subscription.trial_ends_at && new Date(subscription.trial_ends_at) > new Date() && (
                      <p><strong>Trial ends:</strong> {new Date(subscription.trial_ends_at).toLocaleDateString()}</p>
                    )}
                    {subscription.ends_at && (
                      <p><strong>Ends:</strong> {new Date(subscription.ends_at).toLocaleDateString()}</p>
                    )}
                  </div>

                  <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                    {isActive && (
                      <>
                        <select 
                          onChange={(e) => handleChangePlan(subscription.id, e.target.value)}
                          disabled={actionLoading === subscription.id}
                          className="form-control"
                          defaultValue=""
                        >
                          <option value="" disabled>Change Plan...</option>
                          {plans.filter(p => p.id !== subscription.plan_id).map(p => (
                            <option key={p.id} value={p.id}>
                              {p.name} (${p.price}/{p.billing_interval})
                            </option>
                          ))}
                        </select>

                        <button
                          onClick={() => handleCancel(subscription.id)}
                          disabled={actionLoading === subscription.id}
                          className="btn btn-danger"
                        >
                          {actionLoading === subscription.id ? 'Processing...' : 'Cancel Subscription'}
                        </button>
                      </>
                    )}

                    {isCanceled && (
                      <button
                        onClick={() => handleResume(subscription.id)}
                        disabled={actionLoading === subscription.id}
                        className="btn btn-success"
                      >
                        {actionLoading === subscription.id ? 'Processing...' : 'Resume Subscription'}
                      </button>
                    )}
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}

export default Subscriptions;
