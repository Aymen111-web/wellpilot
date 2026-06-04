import { createRouter, createWebHistory } from 'vue-router';
import Home from '../pages/Home.vue';
import Assessment from '../pages/Assessment.vue';
import Dashboard from '../pages/Dashboard.vue';
import AiCoach from '../pages/AiCoach.vue';
import Resorts from '../pages/Resorts.vue';
import Challenges from '../pages/Challenges.vue';
import Login from '../pages/Login.vue';

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/assessment',
    name: 'Assessment',
    component: Assessment
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard
  },
  {
    path: '/ai-coach',
    name: 'AiCoach',
    component: AiCoach
  },
  {
    path: '/resorts',
    name: 'Resorts',
    component: Resorts
  },
  {
    path: '/challenges',
    name: 'Challenges',
    component: Challenges
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  }
});

router.beforeEach((to, from, next) => {
  const nickname = localStorage.getItem('wellpilot_nickname');
  if (!nickname && to.path !== '/login') {
    next('/login');
  } else if (nickname && to.path === '/login') {
    next('/dashboard');
  } else {
    next();
  }
});

export default router;
