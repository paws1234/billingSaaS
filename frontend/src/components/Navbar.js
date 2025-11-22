import React from 'react';
import { Link } from 'react-router-dom';

function Navbar({ user, onLogout }) {
  return (
    <nav className="navbar">
      <div className="navbar-brand">Billing Portal</div>
      <ul className="navbar-nav">
        <li><Link to="/" className="nav-link">Dashboard</Link></li>
        <li><Link to="/plans" className="nav-link">Plans</Link></li>
        <li><Link to="/subscriptions" className="nav-link">Subscriptions</Link></li>
        <li><Link to="/invoices" className="nav-link">Invoices</Link></li>
        {user.role === 'admin' && (
          <li><Link to="/admin" className="nav-link">Admin</Link></li>
        )}
        <li>
          <button onClick={onLogout} className="nav-link" style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'white' }}>
            Logout ({user.email})
          </button>
        </li>
      </ul>
    </nav>
  );
}

export default Navbar;
