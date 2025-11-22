# 🚀 Deploy to Render + Vercel in 10 Minutes

## Prerequisites
- GitHub account
- Render account (sign up at https://render.com with GitHub)
- Vercel account (sign up at https://vercel.com with GitHub)

---

## Step 1: Push to GitHub (2 minutes)

```powershell
cd c:\Users\Paws\Downloads\billing_docker_full

git init
git add .
git commit -m "Initial commit"

# Your repository is already created at:
# https://github.com/paws1234/billingSaaS

git remote add origin https://github.com/paws1234/billingSaaS.git
git branch -M main
git push -u origin main
```

✅ Code on GitHub!

---

## Step 2: Deploy Backend to Render (5 minutes)

### 2.1 Create Database
1. Go to https://dashboard.render.com
2. **New** → **PostgreSQL**
3. Name: `billing-db`, Plan: **Free** → **Create**

### 2.2 Create Web Service
1. **New** → **Web Service**
2. **Connect GitHub** → Select `billingSaaS`
3. Settings:
   - Name: `billing-api`
   - Root Directory: `app`
   - Environment: **Docker**
   - Dockerfile: `Dockerfile.render`
   - Plan: **Free**

4. **Advanced** → Environment Variables:
   ```env
   APP_NAME=Billing System
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://billing-api.onrender.com
   
   DB_CONNECTION=pgsql
   # Click "Add from Database" and select billing-db
   # Or add manually from billing-db dashboard
   
   STRIPE_KEY=sk_test_YOUR_KEY
   STRIPE_SECRET=sk_test_YOUR_SECRET
   
   CACHE_STORE=file
   SESSION_DRIVER=file
   QUEUE_CONNECTION=database
   
   FRONTEND_URL=https://YOUR-APP.vercel.app
   SANCTUM_STATEFUL_DOMAINS=YOUR-APP.vercel.app
   ```

5. **Create Web Service** → Wait 5-10 min

6. When ready, go to **Shell** → Run:
   ```bash
   php artisan db:seed --force
   ```

✅ Backend live at: `https://billing-api.onrender.com`

Test: `curl https://billing-api.onrender.com/api/plans`

---

## Step 3: Deploy Frontend to Vercel (3 minutes)

1. Go to https://vercel.com/new
2. **Import** → Select `billingSaaS`
3. Settings:
   - Framework: **Create React App** (auto-detected)
   - Root Directory: `frontend` ← **IMPORTANT!**
   - Build: `npm run build` (auto)
   - Output: `build` (auto)

4. Environment Variables:
   ```env
   REACT_APP_API_URL=https://billing-api.onrender.com/api
   ```

5. **Deploy** → Wait 2-3 min

✅ Frontend live at: `https://YOUR-APP.vercel.app`

---

## Step 4: Update Backend with Frontend URL (1 minute)

1. Copy your Vercel URL: `https://YOUR-APP.vercel.app`
2. Go to Render → `billing-api` → **Environment**
3. Update:
   ```env
   FRONTEND_URL=https://YOUR-APP.vercel.app
   SANCTUM_STATEFUL_DOMAINS=YOUR-APP.vercel.app
   ```
4. **Save** (auto-redeploys)

---

## 🎉 DONE! Test Your Live App

**Frontend**: https://YOUR-APP.vercel.app

**Login**:
- Customer: `user@test.com` / `password`
- Admin: `admin@test.com` / `password`

**Test with Stripe**:
- Card: `4242 4242 4242 4242`
- Expiry: Any future date
- CVV: Any 3 digits

---

## Auto-Deploy Setup (Bonus)

Every time you `git push` to GitHub:
- ✅ Render auto-deploys backend
- ✅ Vercel auto-deploys frontend

No manual redeployment needed! Just push code and it's live.

---

## Troubleshooting

**CORS errors?**
- Check `FRONTEND_URL` in Render matches your Vercel URL exactly
- Check `SANCTUM_STATEFUL_DOMAINS` includes your Vercel domain

**500 errors on Render?**
- Check **Logs** tab
- Verify database connection
- Run migrations: Shell → `php artisan migrate --force`

**Vercel build fails?**
- Verify Root Directory is set to `frontend`
- Check build logs for errors

**Cold starts on Render Free?**
- First request after 15 min takes ~30 seconds (normal for free tier)
- Upgrade to paid tier for instant responses

---

## Free Tier Limits

**Render**:
- ✅ 750 hours/month (enough for 1 always-on service)
- ⚠️ Spins down after 15 min inactivity
- ⚠️ PostgreSQL free tier expires after 90 days (backup data!)

**Vercel**:
- ✅ Unlimited deployments
- ✅ 100GB bandwidth/month
- ✅ Always on (no cold starts)

---

## Your Portfolio URLs

After deployment:

**Live Demo**: https://YOUR-APP.vercel.app  
**API**: https://billing-api.onrender.com  
**GitHub**: https://github.com/paws1234/billingSaaS

Add these to your resume/portfolio! 🎯
