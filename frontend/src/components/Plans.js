import React, { useState, useEffect } from 'react';
import api from '../services/api';

function Plans({ user }) {
  const [plans, setPlans] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [checkoutLoading, setCheckoutLoading] = useState(null);

  useEffect(() => {
    fetchPlans();
  }, []);

  const fetchPlans = async () => {
    try {
      const response = await api.get('/plans');
      setPlans(response.data);
    } catch (err) {
      setError('Failed to load plans');
    } finally {
      setLoading(false);
    }
  };

  const handleSubscribe = async (plan) => {
    setCheckoutLoading(plan.id);
    setError('');

    try {
      const response = await api.post('/checkout', {
        plan_id: plan.id,
        provider: 'stripe',
        return_url: window.location.origin + '/subscriptions'
      });

      // Redirect to payment provider
      if (response.data.checkout_url) {
        window.location.href = response.data.checkout_url;
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Checkout failed');
      setCheckoutLoading(null);
    }
  };

  if (loading) return <div className="container"><p>Loading plans...</p></div>;

  return (
    <div className="container">
      <h1>Choose Your Plan</h1>
      {error && <div className="alert alert-error">{error}</div>}
      
      <div className="plans-grid" style={{ 
        display: 'grid', 
        gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))',
        gap: '20px',
        marginTop: '30px'
      }}>
        {plans.map(plan => (
          <div key={plan.id} className="card plan-card" style={{ 
            border: plan.name === 'Pro' ? '2px solid #007bff' : '1px solid #ddd',
            position: 'relative'
          }}>
            {plan.name === 'Pro' && (
              <div style={{
                position: 'absolute',
                top: '-10px',
                right: '10px',
                background: '#007bff',
                color: 'white',
                padding: '5px 15px',
                borderRadius: '20px',
                fontSize: '12px'
              }}>
                Popular
              </div>
            )}
            
            <h2>{plan.name}</h2>
            <div style={{ fontSize: '36px', fontWeight: 'bold', margin: '20px 0' }}>
              ${(plan.amount / 100).toFixed(2)}
              <span style={{ fontSize: '16px', color: '#666' }}>/{plan.interval}</span>
            </div>
            
            <ul style={{ listStyle: 'none', padding: 0, marginBottom: '20px' }}>
              {plan.metadata?.features?.map((feature, index) => (
                <li key={index} style={{ padding: '8px 0', borderBottom: '1px solid #eee' }}>
                  ✓ {feature}
                </li>
              ))}
            </ul>

            <button
              onClick={() => handleSubscribe(plan)}
              disabled={checkoutLoading === plan.id}
              className="btn btn-primary"
              style={{ width: '100%' }}
            >
              {checkoutLoading === plan.id ? 'Processing...' : `Subscribe to ${plan.name}`}
            </button>
          </div>
        ))}
      </div>
    </div>
  );
}

export default Plans;
