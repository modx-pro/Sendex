<h1>Available placeholders in Sendex template</h1>

<table>
    <tr><th colspan="2">sxNewsletter Object</th></tr>
    <tr><td>&#91;&#91;+newsletter.id&#93;&#93;</td><td>[[+newsletter.id]]</td></tr>
    <tr><td>&#91;&#91;+newsletter.name&#93;&#93;</td><td>[[+newsletter.name]]</td></tr>
    <tr><td>&#91;&#91;+newsletter.description&#93;&#93;</td><td>[[+newsletter.description]]</td></tr>
    <tr><td>&#91;&#91;+newsletter.active&#93;&#93;</td><td>[[+newsletter.active]]</td></tr>
    <tr><td>&#91;&#91;+newsletter.template&#93;&#93;</td><td>[[+newsletter.template]]</td></tr>
    <tr><td>&#91;&#91;+newsletter.image&#93;&#93;</td><td>[[+newsletter.image]]</td></tr>
    <tr><td>&#91;&#91;+newsletter.email_subject&#93;&#93;</td><td>[[+newsletter.email_subject]]</td></tr>
    <tr><td>&#91;&#91;+newsletter.email_from&#93;&#93;</td><td>[[+newsletter.email_from]]</td></tr>
    <tr><td>&#91;&#91;+newsletter.email_from_name&#93;&#93;</td><td>[[+newsletter.email_from_name]]</td></tr>
    <tr><td>&#91;&#91;+newsletter.email_reply&#93;&#93;</td><td>[[+newsletter.email_reply]]</td></tr>

    <tr><th colspan="2">sxSubscriber Object</th></tr>
    <tr><td>&#91;&#91;+subscriber.id&#93;&#93;</td><td>[[+subscriber.id]]</td></tr>
    <tr><td>&#91;&#91;+subscriber.newsletter_id&#93;&#93;</td><td>[[+subscriber.newsletter_id]]</td></tr>
    <tr><td>&#91;&#91;+subscriber.user_id&#93;&#93;</td><td>[[+subscriber.user_id]]</td></tr>
    <tr><td>&#91;&#91;+subscriber.email&#93;&#93;</td><td>[[+subscriber.email]]</td></tr>
    <tr><td>&#91;&#91;+subscriber.code&#93;&#93;</td><td>[[+subscriber.code]]</td></tr>

    <tr><th colspan="2">modUser Object (If a subscriber is an authorized user)</th></tr>
    <tr><td>&#91;&#91;+user.id&#93;&#93;</td><td>[[+user.id]]</td></tr>
    <tr><td>&#91;&#91;+user.username&#93;&#93;</td><td>[[+user.username]]</td></tr>
    <tr><td>&#91;&#91;+user.password&#93;&#93;</td><td>[[+user.password]]</td></tr>
    <tr><td>&#91;&#91;+user.class_key&#93;&#93;</td><td>[[+user.class_key]]</td></tr>
    <tr><td>&#91;&#91;+user.active&#93;&#93;</td><td>[[+user.active]]</td></tr>
    <tr><td>&#91;&#91;+user.primary_group&#93;&#93;</td><td>[[+user.primary_group]]</td></tr>
    <tr><td>&#91;&#91;+user.sudo&#93;&#93;</td><td>[[+user.sudo]]</td></tr>

    <tr><th colspan="2">modUserProfile Object (If a subscriber is an authorized user)</th></tr>
    <tr><td>&#91;&#91;+profile.id&#93;&#93;</td><td>[[+profile.id]]</td></tr>
    <tr><td>&#91;&#91;+profile.internalKey&#93;&#93;</td><td>[[+profile.internalKey]]</td></tr>
    <tr><td>&#91;&#91;+profile.fullname&#93;&#93;</td><td>[[+profile.fullname]]</td></tr>
    <tr><td>&#91;&#91;+profile.email&#93;&#93;</td><td>[[+profile.email]]</td></tr>
    <tr><td>&#91;&#91;+profile.phone&#93;&#93;</td><td>[[+profile.phone]]</td></tr>
    <tr><td>&#91;&#91;+profile.mobilephone&#93;&#93;</td><td>[[+profile.mobilephone]]</td></tr>
    <tr><td>&#91;&#91;+profile.dob&#93;&#93;</td><td>[[+profile.dob]]</td></tr>
    <tr><td>&#91;&#91;+profile.gender&#93;&#93;</td><td>[[+profile.gender]]</td></tr>
    <tr><td>&#91;&#91;+profile.address&#93;&#93;</td><td>[[+profile.address]]</td></tr>
    <tr><td>&#91;&#91;+profile.country&#93;&#93;</td><td>[[+profile.country]]</td></tr>
    <tr><td>&#91;&#91;+profile.city&#93;&#93;</td><td>[[+profile.city]]</td></tr>
    <tr><td>&#91;&#91;+profile.state&#93;&#93;</td><td>[[+profile.state]]</td></tr>
    <tr><td>&#91;&#91;+profile.zip&#93;&#93;</td><td>[[+profile.zip]]</td></tr>
    <tr><td>&#91;&#91;+profile.fax&#93;&#93;</td><td>[[+profile.fax]]</td></tr>
    <tr><td>&#91;&#91;+profile.photo&#93;&#93;</td><td>[[+profile.photo]]</td></tr>
    <tr><td>&#91;&#91;+profile.comment&#93;&#93;</td><td>[[+profile.comment]]</td></tr>
    <tr><td>&#91;&#91;+profile.website&#93;&#93;</td><td>[[+profile.website]]</td></tr>
</table>

<hr>

<h1>Link for unsubscribe</h1>
Link must lead to page with Sendex call and contain right sx_action and user code:<br/>
<pre>&#91;&#91;~id_of_resource?scheme=`full`&action=`sx_unsubscribe`&code=`&#91;&#91;+subscriber.code&#93;&#93;`&#93;&#93;</pre>

<br/><br/>
For example:<br/>
<a href="[[~[[++site_start]]?scheme=`full`&sx_action=`unsubscribe`&code=`[[+subscriber.code]]`]]">Unsubscribe from this newsletter</a>
