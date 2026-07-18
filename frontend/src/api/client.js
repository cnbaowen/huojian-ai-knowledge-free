import axios from 'axios'

const client = axios.create({ baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:18000/api', timeout: 15000 })
client.interceptors.request.use((config) => {
  const token = localStorage.getItem('free_api_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

export default client
