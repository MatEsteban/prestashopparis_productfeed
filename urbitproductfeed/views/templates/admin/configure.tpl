{*
*2015-2017 Urb-it
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade Urb-it to newer
* versions in the future. If you wish to customize Urb-it for your
* needs please refer to https://urb-it.com for more information.
*
* @author    Urb-it SA <parissupport@urb-it.com>
* @copyright 2015-2017 Urb-it SA
* @license  http://www.gnu.org/licenses/
*
*}

<style>
*, a{
  font-size: 12px;
}
.bootstrap h6, .bootstrap .h6{
  font-size: 14px;
}

</style>
<div id="config-urbitproductfeed">
  <div class="form-wrapper">
    <ul class="nav nav-tabs">
      <li {if $active == 'intro'}class="active"{/if}><a href="#intro"
                                                        data-toggle="tab">{l s='Presentation' mod='urbitproductfeed'}</a></li>
      <li {if $active == 'account'}class="active"{/if}><a href="#account"
                                                          data-toggle="tab">{l s='Module Configuration' mod='urbitproductfeed'}</a>
      </li>
    </ul>
  <div class="tab-content panel">
      <div id="intro" class="tab-pane {if $active == 'intro'}active{/if}">
            <div id="urbit-theme-texticon_390" class="urbit-theme__widget urbit-theme-texticon ABdmKOduM6 text-center" style="" data-tab-id="intro">
      <h2 class="h2">{l s='Pourquoi devenir notre partenaire ?' mod='urbitproductfeed'}</h2>
      <br><br>
           <div class="row urbit-theme-texticon__element">
          <div class="col col-sm-2 col-xs-12">
              <div class="image-wrapper">
              <img src="https://urb-it.com/fr/wp-content/uploads/sites/4/2017/11/Play.jpg" id="play" width="100px">
          </div>
      </div>
    <div class="col col-sm-8 col-xs-12">
            <div class="title h h6">{l s='POUR OBTENIR DE NOUVEAUX CLIENTS' mod='urbitproductfeed'}</div>
            <div class="text b b3">{l s='Vous augmentez votre zone de chalandise en faisant découvrir vos produits à tous les parisiens, pas uniquement ceux de votre quartier.' mod='urbitproductfeed'}</div>
        </div>
    </div>
    <br>
    <div class="row urbit-theme">
        <div class="col col-sm-2 col-xs-12">
            <div class="image-wrapper">
            <img src="https://urb-it.com/fr/wp-content/uploads/sites/4/2017/11/Satisfaction.jpg" alt="satisfaction" width="100px">
        </div>
    </div>
    <div class="col col-sm-8 col-xs-12">
            <div class="title h h6">{l s='POUR OFFRIR UNE EXPÉRIENCE CLIENT EXCEPTIONNELLE ET PERSONNALISÉE' mod='urbitproductfeed'}</div>
            <div class="text b b3">{l s='Différenciez-vous avec Urb-it en répondant aux nouvelles habitudes des parisiens qui ont soif de liberté et de flexibilité dans un monde où tout va très vite. Avec Urb-it, soyez sûr(e) que votre client recevra le même service que dans votre boutique, de l’achat jusqu’à la remise du paquet en passant par le paiement très sécurisé. Urb-it travail en marque blanche avec les marques pour leur donner un maximum de visibilité. Nos Urbers ne sont donc pas logotés durant les urbs. Ils sont habillés en civil.' mod='urbitproductfeed'}</div>
        </div>
    </div>
    <br><br>
    <div class="row urbit-theme-texticon__element">
        <div class="col col-sm-2 col-xs-12">
          <br>
            <div class="image-wrapper">
             <img src="https://urb-it.com/fr/wp-content/uploads/sites/4/2017/11/Rotation.jpg" alt="Rotation" width="90px">
            </div>
        </div>
        <br>
    <div class="col col-sm-8 col-xs-12">
        <div class="title h h6">{l s='POUR OPTIMISER LA ROTATION DE VOS STOCKS ET GÉNÉRERER DU DRIVE TO STORE' mod='urbitproductfeed'}</div>
        <div class="text b b3">{l s='En ajoutant Urb-it à votre stratégie multicanale de vente pour votre magasin, vos stocks tourneront plus rapidement. Le fait de ne pas forcément avoir le temps de se rendre dans votre boutique ne sera plus une excuse pour ne pas acheter vos produits.' mod='urbitproductfeed'}</div>
        </div>
    </div>
  </div>
  <br><br>
  <div class="row text-center">
      <div class="col col-sm-12">
      <h2 class="h2">{l s='Comment ça marche ?' mod='urbitproductfeed'}</h2>
  </div>
  <div class="col col-sm-12">
      <div class="row urbit-theme-circle__container">
        <div class="col col-sm-4 text-center urbit-theme-circle__element numerated">
        <div class="image image-small">
          <img src="https://urb-it.com/fr/wp-content/uploads/sites/4/2017/10/Onboarding_1_500x500px_2-1.gif" alt="Un client achète un produit de votre boutique sur l’application, sur votre site e-commerce ou directement dans votre magasin." width="100px">
        </div>
        <div class="data">
          <span class="num">1</span>
      <div class="title">{l s='Un client achète un produit de votre boutique sur l’application, sur votre site e-commerce ou directement dans votre magasin.' mod='urbitproductfeed'}</div>
      <div class="text"></div>
      </div>
  </div>
            <div class="col col-sm-4 text-center urbit-theme-circle__element numerated">
              <div class="image image-small">
                <img src="https://urb-it.com/fr/wp-content/uploads/sites/4/2017/10/Onboarding_2_500x500px-3.gif" alt="Un Urber va chercher vos achats dans votre boutique." width="100px">
              </div>
      <div class="data">
          <span class="num">2</span>
          <div class="title">{l s='Un Urber va chercher vos achats dans votre boutique.' mod='urbitproductfeed'}</div>
          <div class="text"></div>
        </div>
    </div>

      <div class="col col-sm-4 text-center urbit-theme-circle__element numerated">
          <div class="image image-small">
              <img src="https://urb-it.com/fr/wp-content/uploads/sites/4/2017/10/Onboarding_3_500x500px-2.gif" alt="Votre Urber apporte vos produits à vos clients exactement à l’heure et à l’endroit qu’ils ont choisi. " width="100px">
              </div>
              <div class="data">
              <span class="num">3</span>
              <div class="title">{l s='Votre Urber apporte vos produits à vos clients exactement à l’heure et à l’endroit qu’ils ont choisi.' mod='urbitproductfeed'}</div>
              <div class="text"></div>
              </div>
          </div>
        </div>
      </div>
      </div>
      <br><br>
        <div class="ccol-md-4 text-center">
            <a href="https://urb-it.com/fr/contact-us/" target="_blank"><button type="button" class="btn btn-info btn-lg">{l s='Contact Us' mod='urbitproductfeed'}</button></a>
        </div>

  </div>
      <div id="account" class="tab-pane {if $active == 'account'}active{/if}">
        <div class="alert alert-info">{l s='Please fill in your credentials to use the module' mod='urbitproductfeed'}</div>
        <div class="form-group" data-tab-id="account">
            {$config}
          </div>
      </div>

  </div>
</div>
