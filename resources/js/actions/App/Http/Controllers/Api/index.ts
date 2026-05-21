import AuthController from './AuthController'
import PaymentsController from './PaymentsController'
import MeController from './MeController'
import WalletController from './WalletController'
import DownloadController from './DownloadController'

const Api = {
    AuthController: Object.assign(AuthController, AuthController),
    PaymentsController: Object.assign(PaymentsController, PaymentsController),
    MeController: Object.assign(MeController, MeController),
    WalletController: Object.assign(WalletController, WalletController),
    DownloadController: Object.assign(DownloadController, DownloadController),
}

export default Api