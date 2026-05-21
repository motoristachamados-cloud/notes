import Api from './Api'
import Auth from './Auth'
import Settings from './Settings'

const Controllers = {
    Api: Object.assign(Api, Api),
    Auth: Object.assign(Auth, Auth),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers