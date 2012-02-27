<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>

	<xsl:template match="*" mode="h-user-block">
		<div class="userblock-container">
			<div class="picture">
				<xsl:attribute name="style">
					<xsl:text>background-image:url(</xsl:text>
					<xsl:apply-templates mode="h-user-image" select="." >
						<xsl:with-param name="size" select="'small'" />
						<xsl:with-param name="mode" select="'url'" />
					</xsl:apply-templates>
					<xsl:text>)</xsl:text>
				</xsl:attribute>
			</div>
			<div class="navigation">
				<div>
					<a href="{&prefix;}user/{@id}">профиль</a>
				</div>
				<div>
					<a href="{&prefix;}blog/{@nickname}">блог</a>
				</div>
				<div>
					<a href="{&prefix;}blog/{@nickname}/post">записать мысль</a>
				</div>
				<div>
					<a href="{&prefix;}logout">выход</a>
				</div>
			</div>
		</div>
		
	</xsl:template>
	<xsl:template match="module[@name='users' and @action='show' and not(@mode)]" mode="p-module">
		<xsl:variable name="profile" select="profile" />
		<input type="hidden" name="id" value="{$profile/@id}" />
		<div class="p-user-show-image">
			<img src="{$profile/@picture}?{$profile/@lastSave}" alt="[Image]" />
		</div>
		<div class="p-user-show-text">
			<h1>
				<xsl:value-of select="$profile/@nickname"/>
			</h1>

			<div class="p-user-show-text-role">
				<xsl:value-of select="$profile/@rolename"/>
			</div>

			<xsl:if test="
				($profile/@id = &current_profile;/@id) or
				(&access;/users_edit and (&access;/users_edit/@max_role >= $profile/@role))
				">
				<div class="p-user-show-text-edit">
					<a href="{$profile/@path_edit}">Редактировать профиль</a>
				</div>
			</xsl:if>

		</div>
	</xsl:template>


	<xsl:template match="module[@name='users' and @action='edit']" mode="p-module">
		<xsl:variable name="profile" select="profile"/>
		<form method="post" enctype="multipart/form-data" action="{&prefix;}user/{$profile/@id}">
			<input type="hidden" name="writemodule" value="ProfileWriteModule" />
			<input type="hidden" name="id" value="{$profile/@id}" />
			<div class="form-group">
				<h2>Информация</h2>
				<div class="form-field">
					<label>Почта</label>
					<b>
						<xsl:value-of select="$profile/@email"></xsl:value-of>
					</b>
				</div>
				<xsl:if test="not($profile/@id=&current_profile;/@id)">
					<div class="form-field">
						<label>Роль</label>
						<select name="role">
							<xsl:for-each select="roles/item">
								<option value="{@id}">
									<xsl:if test="$profile/@role=current()/@id">
										<xsl:attribute name="selected"/>
									</xsl:if>
									<xsl:value-of select="@title" />
								</option>
							</xsl:for-each>
						</select>
					</div>
				</xsl:if>
				<div class="form-field">
					<label>Аватар</label>
					<input type="file" name="picture"></input>
				</div>
			</div>
			<div class="form-control">
				<input type="submit" value="Сохранить информацию"/>
			</div>
		</form>
	</xsl:template>

	<xsl:template match="module[@name='users' and @action='edit' and @mode='notifications']" mode="p-module">
		<h2>Настройка уведомлений</h2>
		<form method="post" action="">
			<input type="hidden" name="writemodule" value="NotifyWriteModule"/>
			<input type="hidden" name="id" value="{user/@id}" />
			<div class="form-group">
				<table class="p-user-edit-notify_rules">
					<thead>
						<tr>
							<th>Уведомлять</th>
							<th>по email</th>
							<th>сообщением</th>
						</tr>
					</thead>
					<tbody>
						<xsl:apply-templates select="notify_rules/*" mode="p-user-edit-notify_rule"/>
					</tbody>
				</table>
			</div>
			<div class="form-control">
				<input type="submit" value="Сохранить информацию"/>
			</div>
		</form>
	</xsl:template>

	<xsl:template match="*" mode="p-user-edit-notify_rule">
		<tr>
			<td>
				<xsl:choose>
					<xsl:when test="name()='event_comment'">о комментариях к моим записям</xsl:when>
					<xsl:when test="name()='comment_answer'">об ответах на мои комментарии</xsl:when>
					<xsl:when test="name()='new_message'">о новых личных сообщениях</xsl:when>
					<xsl:when test="name()='new_friend'">о новых поклонниках</xsl:when>
					<xsl:when test="name()='whats_new'">о событиях</xsl:when>
					<xsl:when test="name()='global_objects_comments'">о комментариях к отслеживаемым книгам и авторам</xsl:when>
					<xsl:when test="name()='global_new_reviews'">о рецензиях на отслеживаемые книги</xsl:when>
					<xsl:when test="name()='global_new_genres'">о новых книгах в отслеживаемых жанрах</xsl:when>
					<xsl:when test="name()='global_new_authors'">о новых книгах отслеживаемых авторов</xsl:when>
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</td>
			<td class="p-user-edit-notify_rules-checkbox">
				<input type="checkbox">
					<xsl:attribute name="name">
						<xsl:value-of select="name()"/>[email]
					</xsl:attribute>
					<xsl:if test="email/@cant_be_changed='1'">
						<xsl:attribute name="disabled"/>
					</xsl:if>
					<xsl:if test="email/@enabled='1'">
						<xsl:attribute name="checked"/>
					</xsl:if>
				</input>
			</td>
			<td class="p-user-edit-notify_rules-checkbox">
				<input type="checkbox">
					<xsl:attribute name="name">
						<xsl:value-of select="name()"/>[notify]
					</xsl:attribute>
					<xsl:if test="notify/@cant_be_changed='1'">
						<xsl:attribute name="disabled"/>
					</xsl:if>
					<xsl:if test="notify/@enabled='1'">
						<xsl:attribute name="checked"/>
					</xsl:if>
				</input>
			</td>
		</tr>
	</xsl:template>

	<xsl:template name="profile_edit_cityLoader">
		<xsl:param name="current_city"></xsl:param>
		<div class="form-field">
			<label>Страна:</label>
			<div id="counry_div">загружаем...</div>
		</div>
		<div class="form-field">
			<label>Город:</label>
			<div id="city_div">загружаем...</div>
		</div>
		<script>
      profileModule_cityInit('counry_div','city_div','
			<xsl:value-of select="$current_city"/>');
		</script>
	</xsl:template>

</xsl:stylesheet>
