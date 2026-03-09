<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories\Herd;

use App\BaseAccess;
use App;
use DB;
use Session;
use App\Models\Herd;
use App\Repositories\Herd\HerdUAccess;
use App\Repositories\Herd\HerdHistoryAccess;
use App\Repositories\Formulas;
use App\Repositories\Transactions\PoolTAccess;
use App\Repositories\Transactions\WalletAccess;
use App\Repositories\Transactions\TransactionAccess;
use App\Repositories\User\Useraccess;
use URL;

/**
 * Description of Useraccess
 *
 * @author Asif
 */
class Herdaccess extends BaseAccess {

    private $herd;
    private $formulas;
    private $transactionAccess;
    private $PoolTAccess;
    private $walletTransactions;

    public function __construct() {

        $this->herd = new Herd();
        $this->formulas = new Formulas();
        $this->transactionAccess = new TransactionAccess();
        $this->PoolTAccess = new PoolTAccess();
    }

    public function addHerd($user_id, $icon_id, $icon_price) {
        $this->herd->user_id = $user_id;
        $this->herd->icon_id = $icon_id;
        $this->herd->init_price = $icon_price;
        $this->herd->icon_price = $icon_price;
        $this->herd->save();
        return $insertedId = $this->herd->herd_id;
    }

    public function updateHerd($userId, $iconId, $pledge_amount, $store_name) {
        $arr = array();
        $constants = config('constants.shopzit_gamification');

        $user_wallet = DB::table('users')->where('user_id', $userId)->value('user_wallet');

        $pledge_price = $pledge_amount / $constants['ONE_PLEDGE_PRICE'];
        if ($user_wallet < $pledge_price) {
            $arr['status'] = 'error';
            $arr['message'] = 'Add more zit in your wallet or decrease pledge amount';
        } else {
            $aip = $this->formulas->getPercentageAmount($pledge_price, $constants['AIP_PRICE_INCREASE_PERCENT']);
            $herd_id = $this->herd->where('user_id', $userId)->where('icon_id', $iconId)->value('herd_id');
            if (!$herd_id) {
                $herd_id = $this->addHerd($userId, $iconId, $aip);
                $herdHistoryObj = new HerdHistoryAccess();
                $herdHistoryObj->addHerdHistory($herd_id, $aip, 0, $aip, 'Pledge');
            } else {
                //DB::table('herds')->where('herd_id', $herd_id)->increment('icon_price', $aip);
                $herd_data = DB::table('herds')->where('herd_id', $herd_id)->first();
                $icon_price = $herd_data->icon_price + $aip;
                DB::table('herds')->where('herd_id', $herd_id)->update(array('icon_price' => $icon_price));
                $herdHistoryObj = new HerdHistoryAccess();
                $herdHistoryObj->addHerdHistory($herd_id, $aip, 0, $icon_price, 'Pledge');

                //increase icon price on 2nd level
                DB::table('herds')->join('herd_users', 'herds.herd_id', '=', 'herd_users.user_herd_id')->where('herd_users.seller_herd_id', $herd_id)->increment('herds.icon_debt', $aip);
                $herd_users = DB::table('herds')->join('herd_users', 'herds.herd_id', '=', 'herd_users.user_herd_id')->where('herd_users.seller_herd_id', $herd_id)->select('herds.herd_id', 'herds.icon_price')->get();
                $userName = DB::table('users')->where('user_id', $userId)->value('name');
                foreach ($herd_users as $herd_user) {
                    $herdHistoryObj = new HerdHistoryAccess();
                    $herdHistoryObj->addHerdHistory($herd_user->herd_id, 0, $aip, $herd_user->icon_price, $userName);
                }
                $shv = $this->formulas->getSHV($iconId);
                DB::table('store_icons')->where('icon_id', $iconId)->update(array('shv' => $shv));
            }
            if ($herd_id) {
                $trans_id = $this->transactionAccess->addTransaction('pledge', $pledge_price, $herd_id, $userId, $iconId);
                if ($trans_id) {
                    $walletTransactions = new WalletAccess();
                    $walletTransactions->debitWalletWithZit($pledge_price, $userId, 'debit', 'Pledged support for ' . $store_name, $trans_id);

                    $transAmount = $this->formulas->getPercentageAmount($pledge_price, $constants['TRANSACTION_CHARGES_PERCENTAGE_P']);
                    $this->PoolTAccess = new PoolTAccess();
                    $this->PoolTAccess->creditPool($transAmount, 'credit', $constants['TRANSACTION_POOL_ID'], $trans_id);

                    $operationAmount = $this->formulas->getPercentageAmount($pledge_price, $constants['TRANSACTION_CHARGES_PERCENTAGE_P_OP']);
                    $this->PoolTAccess = new PoolTAccess();
                    $this->PoolTAccess->creditPool($operationAmount, 'credit', $constants['OPERATION_POOL_ID'], $trans_id);

                    $burnAmount = $this->formulas->getPercentageAmount($pledge_price, $constants['TRANSACTION_CHARGES_PERCENTAGE_P_BP']);
                    $this->PoolTAccess = new PoolTAccess();
                    $this->PoolTAccess->creditPool($burnAmount, 'credit', $constants['BURN_POOL_ID'], $trans_id);

                    $cashbackAmount = $this->formulas->getPercentageAmount($pledge_price, $constants['PLEDGE_AMOUNT_GO_TO_CASHBACK_POOL']);
                    $this->PoolTAccess = new PoolTAccess();
                    $this->PoolTAccess->creditPool($cashbackAmount, 'credit', $constants['CASHBACK_POOL_ID'], $trans_id);

                    $arr['status'] = 'success';
                    $arr['message'] = 'Pledge Successful';
                } else {
                    $arr['status'] = 'error';
                    $arr['message'] = 'Cannot add transaction';
                }
            } else {
                $arr['status'] = 'error';
                $arr['message'] = 'Cannot add pledge';
            }
        }
        return $arr;
    }

    public function getHerd($herdId, $userType) {
        $userHerd = $this->herd->where('herd_id', $herdId)->first();
        ///$userHerd=$userHerd->getOriginal();
        //echo __LINE__;
        //$array = get_object_vars($userHerd);
        //print_rr($userHerd); exit;

        if ($userHerd) {
            $userHerd->herdInfo = $this->getHerdInfo($userHerd, $userType);
            return $userHerd;
        } else
            return false;
    }

    public function getUserHerds($userID) {
        $constants = config('constants.shopzit_gamification');
        //$userHerds = $this->herd->where('user_id', $userID)->paginate($constants['PAGINATION_LIMIT']);
        $userHerds = $this->herd->where('user_id', $userID)->get();
        if ($userHerds)
            return $userHerds;
        else
            return false;
    }

    public function getUserHerdsCount($userID) {
        $userHerds = $this->herd->where('user_id', $userID)->count();
        if ($userHerds)
            return $userHerds;
        else
            return 0;
    }

    public function getHerdInfo($herd, $userType) {
        $herdInfo = array();
        $herdInfo['inHerd'] = DB::table('herd_users')->where('user_herd_id', $herd->herd_id)->count();
        $herdInfo['partOfHerd'] = DB::table('herd_users')->where('seller_herd_id', $herd->herd_id)->count();
        $storeInfo = DB::table('stores')->select('store_id', 'store_slug', 'store_logo')->where('store_icon', $herd->icon_id)->first();
        $herdInfo['storeImageId'] = !empty($storeInfo->store_logo) ? $storeInfo->store_logo : '';
        $herdInfo['storeId'] = $storeInfo->store_id;
        $herdInfo['storeSlug'] = $storeInfo->store_slug;
        $herdInfo['noOfProducts'] = DB::table('products')->where('store_id', $storeInfo->store_id)->where('status', '1')->count();
        $herdInfo['lastTransaction'] = DB::table('transactions')->join('herd_users', 'transactions.trans_data_id', '=', 'herd_users.seller_herd_id')->where('transactions.trans_type', 'icon')->where('herd_users.seller_herd_id', $herd->herd_id)->orderBy('transactions.created_at', 'desc')->value('transactions.trans_amount');
        if ($userType == 'other') {
            $userId = getCurrentUserId();
            $herdInfo['userIconAvailable'] = 'no';
            $user_herd_id = DB::table('herds')->where('icon_id', $herd->icon_id)->where('user_id', $userId)->value('herd_id');
            if ($user_herd_id) {
                $herdUsers = DB::table('herd_users')->where([['user_herd_id', $user_herd_id], ['seller_herd_id', $herd->herd_id]])->orWhere([['user_herd_id', $herd->herd_id], ['seller_herd_id', $user_herd_id]])->value('user_herd_id');
                if (!$herdUsers)
                    $herdInfo['userIconAvailable'] = 'yes';
            }
        }
        return $herdInfo;
    }

    public function iconPurchase($sellerHerdId) {
        $arr = array();
        $constants = config('constants.shopzit_gamification');
        $userId = getCurrentUserId();

        $sellerHerd = DB::table('herds')->where('herd_id', $sellerHerdId)->first();
        if (!empty($sellerHerd)) {
            $userHerd = DB::table('herds')->where('icon_id', $sellerHerd->icon_id)->where('user_id', $userId)->first();
            if (empty($userHerd)) {
                $arr['status'] = 'error';
                $arr['message'] = 'Pledge for support for this icon first';
                return $arr;
            }

            //checking if icon already purchased
            $herdUsers = DB::table('herd_users')->where([['user_herd_id', $userHerd->herd_id], ['seller_herd_id', $sellerHerdId]])->orWhere([['user_herd_id', $sellerHerdId], ['seller_herd_id', $userHerd->herd_id]])->value('user_herd_id');
            if ($herdUsers) {
                $arr['status'] = 'error';
                $arr['message'] = 'Icon already purchased';
                return $arr;
            }

            //checking user wallet
            $userWallet = DB::table('users')->where('user_id', $userId)->value('user_wallet');
            $iconPrice = $sellerHerd->icon_price + $sellerHerd->icon_debt;
            if ($userWallet < $iconPrice) {
                $arr['status'] = 'error';
                $arr['message'] = 'Add more zit in your wallet to add this to your herd';
                return $arr;
            }

            //creating transaction
            $trans_id = $this->transactionAccess->addTransaction('icon', $iconPrice, $sellerHerdId, $userId, $sellerHerd->icon_id);
            if (!$trans_id) {
                $arr['status'] = 'error';
                $arr['message'] = 'Cannot add transaction';
                return $arr;
            }

            //add seller in user herd
            $herdUsers = new HerdUAccess();
            $herdUsers->addHerdUsers($userHerd->herd_id, $sellerHerdId);

            $icon_name = DB::table('store_icons')->where('icon_id', $sellerHerd->icon_id)->value('name');
            $seller_name = DB::table('users')->where('user_id', $sellerHerd->user_id)->value('name');

            //reducing zit from user wallet
            $walletTransactions = new WalletAccess();
            $walletTransactions->debitWalletWithZit($iconPrice, $userId, 'debit', 'Purchased icon ' . ucwords($icon_name) . ' from ' . $seller_name, $trans_id);

            //adding zit in transaction pool
            $transAmount = $this->formulas->getPercentageAmount($iconPrice, $constants['TRANSACTION_CHARGES_PERCENTAGE_IP']);
            $this->PoolTAccess = new PoolTAccess();
            $this->PoolTAccess->creditPool($transAmount, 'credit', $constants['TRANSACTION_POOL_ID'], $trans_id);

            //adding zit in operation pool
            $operationAmount = $this->formulas->getPercentageAmount($iconPrice, $constants['TRANSACTION_CHARGES_PERCENTAGE_IP_OP']);
            $this->PoolTAccess = new PoolTAccess();
            $this->PoolTAccess->creditPool($operationAmount, 'credit', $constants['OPERATION_POOL_ID'], $trans_id);

            //adding zit in burn pool
            $burnAmount = $this->formulas->getPercentageAmount($iconPrice, $constants['TRANSACTION_CHARGES_PERCENTAGE_IP_BP']);
            $this->PoolTAccess = new PoolTAccess();
            $this->PoolTAccess->creditPool($burnAmount, 'credit', $constants['BURN_POOL_ID'], $trans_id);

            //adding zit in cashback pool
            //$cashbackAmount = $this->formulas->getPercentageAmount($iconPrice, $constants['ICON_PURCHASE_CASHBACK_POOL']);
            //$this->PoolTAccess = new PoolTAccess();
            //$this->PoolTAccess->creditPool($cashbackAmount, 'credit', $constants['CASHBACK_POOL_ID'], $trans_id);
            //adding zit in seller wallet
            $sellerWalletAmount = $this->formulas->getPercentageAmount($iconPrice, $constants['ICON_PURCHASE_USER_WALLET_AMOUNT']);
            $buyer_name = DB::table('users')->where('user_id', $userId)->value('name');
            $walletTransactions = new WalletAccess();
            $walletTransactions->creditWalletWithZit($sellerWalletAmount, $sellerHerd->user_id, 'credit', ucwords($icon_name) . ' icon purchased by ' . $buyer_name, $trans_id);

            //clearing debt and increasing icon price
            $perc_inc = $this->formulas->getIconPriceIncrease($sellerHerd->icon_price, $iconPrice);
            DB::table('herds')->where('herd_id', $sellerHerdId)->update(array('icon_price' => $iconPrice, 'icon_debt' => $burnAmount, 'perc_inc_dec' => $perc_inc));
            $buyer_name = DB::table('users')->where('user_id', $userId)->value('name');
            $herdHistoryObj = new HerdHistoryAccess();
            $herdHistoryObj->addHerdHistory($sellerHerdId, 0, $burnAmount, $sellerHerd->icon_price, $buyer_name);
            DB::table('herds')->where('herd_id', $sellerHerdId)->increment('total_earning', $sellerWalletAmount);

            //increase icon price on 2nd level
            DB::table('herds')->join('herd_users', 'herds.herd_id', '=', 'herd_users.user_herd_id')->where('herd_users.user_herd_id', '<>', $userHerd->herd_id)->where('herd_users.seller_herd_id', $sellerHerdId)->increment('herds.icon_debt', $burnAmount);
            $herd_users = DB::table('herds')->join('herd_users', 'herds.herd_id', '=', 'herd_users.user_herd_id')->where('herd_users.user_herd_id', '<>', $userHerd->herd_id)->where('herd_users.seller_herd_id', $sellerHerdId)->select('herds.herd_id', 'herds.icon_price')->get();

            $userName = DB::table('users')->where('user_id', $sellerHerd->user_id)->value('name');
            foreach ($herd_users as $herd_user) {
                $herdHistoryObj = new HerdHistoryAccess();
                $herdHistoryObj->addHerdHistory($herd_user->herd_id, 0, $burnAmount, $herd_user->icon_price, $userName);
            }

            //update seeler herd value
            $shv = $this->formulas->getSHV($sellerHerd->icon_id);
            DB::table('store_icons')->where('icon_id', $sellerHerd->icon_id)->update(array('shv' => $shv));
            $arr['status'] = 'success';
            $arr['message'] = 'Icon added to your herd';
            $arr['herd_details'] = $data = $this->getHerd($sellerHerdId, 'other');

            $data = $data->getOriginal();
            $arr['herd_info'] = $data;
            
            $z_animal = getZAnimalById($data['icon_id']);
            $buyer = getUserById($userId);                
            $description = $subject = "Your Zanimal $z_animal->name has increased by $perc_inc due to an increase in price of $buyer->name's $z_animal->name.";

            addNotifications($sellerHerd->user_id, $userId, $subject, $description, URL::to('/user-profile'));

            $userobj = new Useraccess();
            $userData = $userobj->getUserDataById($userId);
            $arr['user_wallet'] = !empty($userData->user_wallet) ? number_format_short($userData->user_wallet) : 0;
        } else {
            $arr['status'] = 'error';
            $arr['message'] = 'Icon seller not found';
        }
        return $arr;
    }

    public function iconDetails($userHerdId) {
        $arr = array();
        $constants = config('constants.shopzit_gamification');
        $userId = getCurrentUserId();

        $userHerd = DB::table('herds')->where('herd_id', $userHerdId)->first();
        if (empty($userHerd)) {
            $arr['status'] = 'error';
            $arr['message'] = 'Icon user not found';
            return $arr;
        }
        if ($userId && $userId == $userHerd->user_id) {
            $arr['status'] = 'success';

            $arr['herd_details'] = $userHerd;
            $arr['icon_name'] = DB::table('store_icons')->where('icon_id', $userHerd->icon_id)->value('name');
            //get transactions history
            $transactions = DB::table('transactions')->whereRaw('trans_icon_id = ? AND (trans_user_id = ? OR trans_data_id = ?)', [$userHerd->icon_id, $userId, $userHerdId])->paginate($constants['PAGINATION_LIMIT']);
            $q = [];
            $q['user_herd_id'] = $userHerdId;
            $transactions_pagination = $transactions->appends($q)->links('pagination.defaultpagination');
            $arr['transactions_pagination'] = (string) $transactions_pagination;
            foreach ($transactions as $transaction) {
                $transaction->date_display = date('d M Y', strtotime($transaction->created_at));
                if ($transaction->trans_type == 'pledge') {
                    $transaction->desc = 'Pledged Icon ' . $arr['icon_name'];
                } else if ($transaction->trans_type == 'icon') {
                    if ($transaction->trans_user_id != $userId) {
                        $user_name = DB::table('users')->where('user_id', $transaction->trans_user_id)->value('name');
                        $transaction->desc = $user_name . ' purchased your icon';
                    } else {
                        $user_name = DB::table('users')->join('herds', 'users.user_id', '=', 'herds.user_id')->where('herds.herd_id', $transaction->trans_data_id)->value('users.name');
                        $transaction->desc = 'Purchased ' . $user_name . ' icon';
                    }
                }
            }
            $arr['transactions'] = $transactions;

            //get AIP history
            $aip_history = DB::table('herd_aip_history')->where('herd_id', $userHerdId)->get();
            foreach ($aip_history as $aip_hist) {
                $aip_hist->date_display = date('d M Y', strtotime($aip_hist->created_at));
            }
            $arr['aip_history'] = $aip_history;

            //get users in your herd/herd you are part of
            $inHerd = DB::table('herd_users')->join('herds', 'herd_users.seller_herd_id', '=', 'herds.herd_id')->join('users', 'herds.user_id', '=', 'users.user_id')->where('herd_users.user_herd_id', $userHerdId)->select('users.user_id', 'users.name', 'users.user_avatar', 'herds.icon_price')->get();

            foreach ($inHerd as $user) {
                if ($user->user_avatar && $user->user_avatar != NULL) {
                    $user->user_img = getAttachmentUrlById((int) $user->user_avatar, 'avatar');
                } else {
                    $user->user_img = '';
                    $user->name_init = substr($user->name, 0, 1);
                }
            }
            $arr['inHerd'] = $inHerd;

            $partOfHerd = DB::table('herd_users')->join('herds', 'herd_users.user_herd_id', '=', 'herds.herd_id')->join('users', 'herds.user_id', '=', 'users.user_id')->where('seller_herd_id', $userHerdId)->select('users.user_id', 'users.name', 'users.user_avatar', 'herds.icon_price')->get();
            foreach ($partOfHerd as $user) {
                if ($user->user_avatar && $user->user_avatar != NULL) {
                    $user->user_img = getAttachmentUrlById($user->user_avatar, 'avatar');
                } else {
                    $user->user_img = '';
                    $user->name_init = substr($user->name, 0, 1);
                }
            }
            $arr['partOfHerd'] = $partOfHerd;
        } else {
            $arr['status'] = 'error';
            $arr['message'] = 'Login to view details';
        }

        return $arr;
    }

    public function iconDetailsTransactions($userHerdId) {
        $arr = array();
        $constants = config('constants.shopzit_gamification');
        $userId = getCurrentUserId();

        $userHerd = DB::table('herds')->where('herd_id', $userHerdId)->first();
        if (empty($userHerd)) {
            $arr['status'] = 'error';
            $arr['message'] = 'Icon user not found';
            return $arr;
        }
        if ($userId && $userId == $userHerd->user_id) {
            $arr['status'] = 'success';
            $arr['herd_details'] = $userHerd;
            $arr['icon_name'] = DB::table('store_icons')->where('icon_id', $userHerd->icon_id)->value('name');
            //get transactions history
            $transactions = DB::table('transactions')->whereRaw('trans_icon_id = ? AND (trans_user_id = ? OR trans_data_id = ?)', [$userHerd->icon_id, $userId, $userHerdId])->paginate($constants['PAGINATION_LIMIT']);
            $q = [];
            $q['user_herd_id'] = $userHerdId;
            $transactions_pagination = $transactions->appends($q)->links('pagination.defaultpagination');
            $arr['transactions_pagination'] = (string) $transactions_pagination;
            foreach ($transactions as $transaction) {
                $transaction->date_display = date('d M Y', strtotime($transaction->created_at));
                if ($transaction->trans_type == 'pledge') {
                    $transaction->desc = 'Pledged Icon ' . $arr['icon_name'];
                } else if ($transaction->trans_type == 'icon') {
                    if ($transaction->trans_user_id != $userId) {
                        $user_name = DB::table('users')->where('user_id', $transaction->trans_user_id)->value('name');
                        $transaction->desc = $user_name . ' purchased your icon';
                    } else {
                        $user_name = DB::table('users')->join('herds', 'users.user_id', '=', 'herds.user_id')->where('herds.herd_id', $transaction->trans_data_id)->value('users.name');
                        $transaction->desc = 'Purchased ' . $user_name . ' icon';
                    }
                }
            }
            $arr['transactions'] = $transactions;
        } else {
            $arr['status'] = 'error';
            $arr['message'] = 'Login to view details';
        }

        return $arr;
    }

    public function iconDetailsAipHistory($userHerdId) {
        $arr = array();
        $constants = config('constants.shopzit_gamification');
        $userId = getCurrentUserId();

        $userHerd = DB::table('herds')->where('herd_id', $userHerdId)->first();
        if (empty($userHerd)) {
            $arr['status'] = 'error';
            $arr['message'] = 'Icon user not found';
            return $arr;
        }
        if ($userId && $userId == $userHerd->user_id) {
            $arr['status'] = 'success';

            //get AIP history
            $aip_history = DB::table('herd_aip_history')->where('herd_id', $userHerdId)->get();
            foreach ($aip_history as $aip_hist) {
                $aip_hist->date_display = date('d M Y', strtotime($aip_hist->created_at));
            }
            $arr['aip_history'] = $aip_history;
            $arr['transactions'] = $transactions;
        } else {
            $arr['status'] = 'error';
            $arr['message'] = 'Login to view details';
        }

        return $arr;
    }

    public function getSupporters($storeId) {
        $data = array();
        // we are getting those users which have pledge of 30 zit or greater

        $iconDetails = DB::table('store_icons')->join('stores', 'store_icons.icon_id', '=', 'stores.store_icon')
                        ->where('stores.store_id', $storeId)
                        ->select('store_icons.icon_id', 'store_icons.shv')->first();
        if (!isset($iconDetails->icon_id)) {
            return false;
        }
        if ($iconDetails->icon_id) {
            $query = DB::table('transactions')->select('trans_user_id', DB::raw('SUM(trans_amount) as total_pledge_amount'))
                            ->where('trans_icon_id', $iconDetails->icon_id)
                            ->where('trans_type', 'pledge')
                            ->havingRaw('SUM(trans_amount) >= ?', [30])
                            ->groupBy('trans_user_id')->get();

            foreach ($query as $q) {
                // if ($q->total_pledge_amount >= 30) {
                $query2 = DB::table('herds')->join('users', 'herds.user_id', '=', 'users.user_id')
                                ->where('users.user_id', $q->trans_user_id)
                                ->where('herds.icon_id', $iconDetails->icon_id)
                                ->select('users.user_id', 'users.name', 'users.user_city_id', 'users.user_avatar', 'herds.herd_id', 'herds.icon_debt', 'herds.icon_id', 'herds.icon_price', 'herds.perc_inc_dec')->first();

                $data[] = $query2;
                //}
            }
            return $data;
        } else {
            return false;
        }
//          
    }

    public function getIconSuperSupporters($storeId) {
//        $qer=$this->getSupporters($storeId);
//        print_rr($qer);
//        echo "----------------------------";
        $constants = config('constants.shopzit_gamification');
        $iconDetails = DB::table('store_icons')->join('stores', 'store_icons.icon_id', '=', 'stores.store_icon')->where('stores.store_id', $storeId)->select('store_icons.icon_id', 'store_icons.shv')->first();
        //$shv = $this->formulas->getSHV($iconDetails->icon_id);
        //DB::table('store_icons')->where('icon_id', $iconDetails->icon_id)->update(array('shv' => $shv));
        //if($shv){
        if ($iconDetails->shv) {
            $superSupporterAmount = $this->formulas->getPercentageAmount($iconDetails->shv, $constants['SUPERSUPPORTER_PERCENT']);
            $superSupporters = DB::table('herds')->join('users', 'herds.user_id', '=', 'users.user_id')->whereRaw('herds.icon_id = ? AND (herds.icon_price - herds.icon_debt) > ?', [$iconDetails->icon_id, $superSupporterAmount])->select('users.user_id', 'users.name', 'users.user_city_id', 'users.user_avatar', 'herds.icon_id', 'herds.icon_price', 'herds.perc_inc_dec')->get();
            //print_rr($superSupporters);
            return $superSupporters;
        } else {
            return false;
        }
    }

}
